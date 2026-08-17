<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ClockOutAttendanceRequest;
use App\Http\Requests\Staff\ReviewLeaveRequest;
use App\Http\Requests\Staff\StoreAttendanceRequest;
use App\Http\Requests\Staff\StoreEmployeeRequest;
use App\Http\Requests\Staff\StoreLeaveRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\LeaveRequestResource;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function __construct(private StaffService $staffService) {}

    public function employees(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:ACTIVE,INACTIVE,SUSPENDED'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return EmployeeResource::collection($this->staffService->employees($filters))
            ->additional(['success' => true, 'message' => 'Employees retrieved successfully.']);
    }

    public function employeeFormData(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->staffService->employeeFormData()]);
    }

    public function storeEmployee(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->staffService->createEmployee($request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Employee profile created successfully.',
            'data' => (new EmployeeResource($employee))->resolve(),
        ], 201);
    }

    public function showEmployee(Employee $employee): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new EmployeeResource($this->staffService->loadEmployee($employee)))->resolve(),
        ]);
    }

    public function updateEmployee(StoreEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee = $this->staffService->updateEmployee($employee, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Employee profile updated successfully.',
            'data' => (new EmployeeResource($employee))->resolve(),
        ]);
    }

    public function archiveEmployee(Employee $employee): JsonResponse
    {
        $this->staffService->archiveEmployee($employee);

        return response()->json(['success' => true, 'message' => 'Employee profile marked inactive.']);
    }

    public function attendance(Request $request)
    {
        $filters = $request->validate([
            'attendance_date' => ['nullable', 'date'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'status' => ['nullable', 'in:PRESENT,LATE,ABSENT,ON_LEAVE'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return AttendanceRecordResource::collection($this->staffService->attendance($filters))
            ->additional(['success' => true, 'message' => 'Attendance records retrieved successfully.']);
    }

    public function storeAttendance(StoreAttendanceRequest $request): JsonResponse
    {
        $record = $this->staffService->recordAttendance($request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully.',
            'data' => (new AttendanceRecordResource($record))->resolve(),
        ], 201);
    }

    public function clockOut(ClockOutAttendanceRequest $request, AttendanceRecord $attendanceRecord): JsonResponse
    {
        $record = $this->staffService->clockOut($attendanceRecord, $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Clock-out recorded successfully.',
            'data' => (new AttendanceRecordResource($record))->resolve(),
        ]);
    }

    public function leaveRequests(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:PENDING,APPROVED,REJECTED,CANCELLED'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return LeaveRequestResource::collection($this->staffService->leaveRequests($filters))
            ->additional(['success' => true, 'message' => 'Leave requests retrieved successfully.']);
    }

    public function storeLeaveRequest(StoreLeaveRequest $request): JsonResponse
    {
        $leave = $this->staffService->createLeaveRequest($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully.',
            'data' => (new LeaveRequestResource($leave))->resolve(),
        ], 201);
    }

    public function reviewLeaveRequest(ReviewLeaveRequest $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $leave = $this->staffService->reviewLeaveRequest($leaveRequest, $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Leave request reviewed successfully.',
            'data' => (new LeaveRequestResource($leave))->resolve(),
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->staffService->summary()]);
    }
}
