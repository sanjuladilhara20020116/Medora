<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffService
{
    public function employees(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);
        $search = trim((string) ($filters['search'] ?? ''));

        return Employee::query()
            ->with(['department:id,code,name', 'user:id,name,username,role_id', 'user.role:id,name'])
            ->withCount(['attendanceRecords', 'leaveRequests'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%")
                        ->orWhereHas('department', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['department_id']), fn ($query) => $query->where('department_id', $filters['department_id']))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($perPage);
    }

    public function loadEmployee(Employee $employee): Employee
    {
        return $employee->load([
            'department:id,code,name',
            'user:id,name,username,role_id',
            'user.role:id,name',
            'createdBy:id,name',
        ])->loadCount(['attendanceRecords', 'leaveRequests']);
    }

    public function createEmployee(array $data, User $user): Employee
    {
        $this->ensureUserCanBeLinked($data['user_id'] ?? null);

        return DB::transaction(function () use ($data, $user) {
            $employee = Employee::create([
                ...$data,
                'employee_code' => null,
                'status' => $data['status'] ?? 'ACTIVE',
                'created_by' => $user->id,
            ]);
            $employee->update([
                'employee_code' => sprintf('EMP-%s-%05d', now()->format('Y'), $employee->id),
            ]);

            return $this->loadEmployee($employee->fresh());
        });
    }

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $this->ensureUserCanBeLinked($data['user_id'] ?? null, $employee->id);
        $employee->update($data);

        return $this->loadEmployee($employee->fresh());
    }

    public function archiveEmployee(Employee $employee): void
    {
        $employee->update(['status' => 'INACTIVE']);
    }

    public function employeeFormData(): array
    {
        return [
            'departments' => Department::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name'])
                ->map(fn (Department $department) => [
                    'id' => $department->id,
                    'code' => $department->code,
                    'name' => $department->name,
                ])->values(),
            'users' => User::query()
                ->with('role:id,name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'username', 'role_id'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role?->name,
                ])->values(),
        ];
    }

    public function attendance(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);

        return AttendanceRecord::query()
            ->with(['employee.department:id,name', 'recordedBy:id,name'])
            ->when(! empty($filters['attendance_date']), fn ($query) => $query->whereDate('attendance_date', $filters['attendance_date']))
            ->when(! empty($filters['employee_id']), fn ($query) => $query->where('employee_id', $filters['employee_id']))
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderByDesc('attendance_date')
            ->orderByDesc('clock_in')
            ->paginate($perPage);
    }

    public function recordAttendance(array $data, User $user): AttendanceRecord
    {
        $employee = $this->activeEmployee((int) $data['employee_id']);
        $date = Carbon::parse($data['attendance_date'])->toDateString();

        if (AttendanceRecord::query()->where('employee_id', $employee->id)->whereDate('attendance_date', $date)->exists()) {
            throw ValidationException::withMessages([
                'employee_id' => ['An attendance record already exists for this employee on the selected date.'],
            ]);
        }

        $status = $data['status'];
        $clockIn = in_array($status, ['ABSENT', 'ON_LEAVE'], true)
            ? null
            : Carbon::parse($data['clock_in'] ?? now());

        $record = AttendanceRecord::create([
            'employee_id' => $employee->id,
            'attendance_date' => $date,
            'clock_in' => $clockIn,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $user->id,
        ]);

        return $record->fresh(['employee.department:id,name', 'recordedBy:id,name']);
    }

    public function clockOut(AttendanceRecord $attendanceRecord, array $data, User $user): AttendanceRecord
    {
        if (in_array($attendanceRecord->status, ['ABSENT', 'ON_LEAVE'], true)) {
            throw ValidationException::withMessages([
                'attendance' => ['An absent or on-leave attendance record cannot be clocked out.'],
            ]);
        }

        if ($attendanceRecord->clock_out) {
            throw ValidationException::withMessages([
                'attendance' => ['This attendance record has already been clocked out.'],
            ]);
        }

        $clockOut = Carbon::parse($data['clock_out'] ?? now());
        if ($attendanceRecord->clock_in && $clockOut->lessThan($attendanceRecord->clock_in)) {
            throw ValidationException::withMessages([
                'clock_out' => ['Clock-out time cannot be before clock-in time.'],
            ]);
        }

        $attendanceRecord->update([
            'clock_out' => $clockOut,
            'notes' => $data['notes'] ?? $attendanceRecord->notes,
            'recorded_by' => $user->id,
        ]);

        return $attendanceRecord->fresh(['employee.department:id,name', 'recordedBy:id,name']);
    }

    public function leaveRequests(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);

        return LeaveRequest::query()
            ->with(['employee.department:id,name', 'reviewedBy:id,name'])
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['employee_id']), fn ($query) => $query->where('employee_id', $filters['employee_id']))
            ->when(! empty($filters['from_date']), fn ($query) => $query->whereDate('end_date', '>=', $filters['from_date']))
            ->when(! empty($filters['to_date']), fn ($query) => $query->whereDate('start_date', '<=', $filters['to_date']))
            ->orderByDesc('requested_at')
            ->paginate($perPage);
    }

    public function createLeaveRequest(array $data): LeaveRequest
    {
        $employee = $this->activeEmployee((int) $data['employee_id']);
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();

        $overlaps = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->exists();
        if ($overlaps) {
            throw ValidationException::withMessages([
                'start_date' => ['This leave period overlaps an existing pending or approved leave request.'],
            ]);
        }

        return DB::transaction(function () use ($data, $employee, $start, $end) {
            $leave = LeaveRequest::create([
                'leave_code' => null,
                'employee_id' => $employee->id,
                'leave_type' => $data['leave_type'],
                'start_date' => $start,
                'end_date' => $end,
                'total_days' => $start->diffInDays($end) + 1,
                'reason' => $data['reason'],
                'status' => 'PENDING',
                'requested_at' => now(),
            ]);
            $leave->update([
                'leave_code' => sprintf('LEV-%s-%05d', now()->format('Y'), $leave->id),
            ]);

            return $leave->fresh(['employee.department:id,name', 'reviewedBy:id,name']);
        });
    }

    public function reviewLeaveRequest(LeaveRequest $leaveRequest, array $data, User $user): LeaveRequest
    {
        if ($leaveRequest->status !== 'PENDING') {
            throw ValidationException::withMessages([
                'leave_request' => ['Only a pending leave request can be reviewed.'],
            ]);
        }

        $leaveRequest->update([
            'status' => $data['decision'],
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'reviewer_comment' => $data['reviewer_comment'] ?? null,
        ]);

        return $leaveRequest->fresh(['employee.department:id,name', 'reviewedBy:id,name']);
    }

    public function summary(): array
    {
        return [
            'active_employees' => Employee::query()->where('status', 'ACTIVE')->count(),
            'clocked_in_today' => AttendanceRecord::query()
                ->whereDate('attendance_date', today())
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->count(),
            'pending_leave_requests' => LeaveRequest::query()->where('status', 'PENDING')->count(),
        ];
    }

    private function activeEmployee(int $employeeId): Employee
    {
        $employee = Employee::query()->whereKey($employeeId)->where('status', 'ACTIVE')->first();
        if (! $employee) {
            throw ValidationException::withMessages([
                'employee_id' => ['The selected employee is not active.'],
            ]);
        }

        return $employee;
    }

    private function ensureUserCanBeLinked(?int $userId, ?int $employeeId = null): void
    {
        if (! $userId) {
            return;
        }

        $linked = Employee::query()
            ->where('user_id', $userId)
            ->when($employeeId, fn ($query) => $query->where('id', '!=', $employeeId))
            ->exists();
        if ($linked) {
            throw ValidationException::withMessages([
                'user_id' => ['This user account is already linked to another employee profile.'],
            ]);
        }
    }
}
