<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\LabRequest;
use App\Models\LeaveRequest;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PrescriptionDispense;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReportService
{
    public function overview(array $range): array
    {
        [$from, $to] = $this->range($range);

        return [
            'period' => $this->period($from, $to),
            'patients' => [
                'total' => Patient::query()->count(),
                'registered_in_period' => Patient::query()->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])->count(),
            ],
            'appointments' => [
                'total' => Appointment::query()->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])->count(),
                'completed' => Appointment::query()->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])->where('status', 'COMPLETED')->count(),
            ],
            'revenue' => [
                'payments_received' => $this->decimal(Payment::query()->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])->sum('amount')),
                'outstanding' => $this->decimal(Invoice::query()->where('status', '!=', 'CANCELLED')->sum('balance')),
            ],
            'laboratory' => [
                'requests' => LabRequest::query()->whereBetween('requested_at', [$from->startOfDay(), $to->endOfDay()])->count(),
                'completed' => LabRequest::query()->whereBetween('requested_at', [$from->startOfDay(), $to->endOfDay()])->where('status', 'COMPLETED')->count(),
            ],
            'pharmacy' => [
                'expired_batches' => MedicineStock::query()->where('quantity_available', '>', 0)->whereDate('expiry_date', '<', today())->count(),
                'dispensations' => PrescriptionDispense::query()->whereBetween('dispensed_at', [$from->startOfDay(), $to->endOfDay()])->count(),
            ],
            'staff' => [
                'active_employees' => Employee::query()->where('status', 'ACTIVE')->count(),
                'pending_leave_requests' => LeaveRequest::query()->where('status', 'PENDING')->count(),
            ],
        ];
    }

    public function patients(array $range): array
    {
        [$from, $to] = $this->range($range);
        $registrations = Patient::query()->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);

        return [
            'period' => $this->period($from, $to),
            'total_patients' => Patient::query()->count(),
            'new_registrations' => $registrations->count(),
            'by_gender' => $this->keyValueCounts(Patient::query()->selectRaw('COALESCE(gender, "Unspecified") as label, COUNT(*) as total')->groupBy('gender')->get()),
            'registrations_by_date' => $this->dailyCounts(Patient::query(), 'created_at', $from, $to),
            'recent_patients' => Patient::query()
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(['id', 'patient_code', 'first_name', 'last_name', 'gender', 'phone', 'created_at'])
                ->map(fn (Patient $patient) => [
                    'patient_code' => $patient->patient_code,
                    'full_name' => trim($patient->first_name.' '.$patient->last_name),
                    'gender' => $patient->gender,
                    'phone' => $patient->phone,
                    'registered_at' => $patient->created_at?->toIso8601String(),
                ])->values(),
        ];
    }

    public function appointments(array $range): array
    {
        [$from, $to] = $this->range($range);
        $appointments = Appointment::query()->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()]);

        return [
            'period' => $this->period($from, $to),
            'total_appointments' => $appointments->count(),
            'by_status' => $this->keyValueCounts((clone $appointments)->selectRaw('status as label, COUNT(*) as total')->groupBy('status')->get()),
            'by_date' => $this->dailyCounts(Appointment::query(), 'appointment_date', $from, $to),
            'by_doctor' => Appointment::query()
                ->with('doctor.user:id,name')
                ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('doctor_id, COUNT(*) as total')
                ->groupBy('doctor_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(fn (Appointment $appointment) => [
                    'label' => $appointment->doctor?->user?->name ?? 'Unassigned doctor',
                    'total' => (int) $appointment->total,
                ])->values(),
        ];
    }

    public function revenue(array $range): array
    {
        [$from, $to] = $this->range($range);
        $payments = Payment::query()->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()]);
        $invoices = Invoice::query()->whereBetween('issued_at', [$from->startOfDay(), $to->endOfDay()]);

        return [
            'period' => $this->period($from, $to),
            'invoiced' => $this->decimal((clone $invoices)->where('status', '!=', 'CANCELLED')->sum('total_amount')),
            'payments_received' => $this->decimal((clone $payments)->sum('amount')),
            'outstanding_balance' => $this->decimal(Invoice::query()->where('status', '!=', 'CANCELLED')->sum('balance')),
            'payments_by_date' => $this->dailySums(Payment::query(), 'paid_at', 'amount', $from, $to),
            'by_payment_method' => $this->keyValueSums((clone $payments)->selectRaw('payment_method as label, SUM(amount) as total')->groupBy('payment_method')->get()),
            'invoice_statuses' => $this->keyValueCounts((clone $invoices)->selectRaw('status as label, COUNT(*) as total')->groupBy('status')->get()),
        ];
    }

    public function pharmacy(array $range): array
    {
        [$from, $to] = $this->range($range);
        $stocks = MedicineStock::query()->with('medicine:id,medicine_code,name')->where('quantity_available', '>', 0);

        return [
            'period' => $this->period($from, $to),
            'active_medicines' => Medicine::query()->where('is_active', true)->count(),
            'inventory_value' => $this->decimal($stocks->get()->sum(fn (MedicineStock $stock) => (float) $stock->quantity_available * (float) $stock->selling_price)),
            'expired_batches' => MedicineStock::query()->where('quantity_available', '>', 0)->whereDate('expiry_date', '<', today())->count(),
            'expiring_batches' => MedicineStock::query()->where('quantity_available', '>', 0)->whereBetween('expiry_date', [today(), today()->addDays(30)])->count(),
            'dispensations_by_date' => $this->dailyCounts(PrescriptionDispense::query(), 'dispensed_at', $from, $to),
            'stock_by_medicine' => MedicineStock::query()
                ->with('medicine:id,medicine_code,name')
                ->where('quantity_available', '>', 0)
                ->selectRaw('medicine_id, SUM(quantity_available) as total')
                ->groupBy('medicine_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(fn (MedicineStock $stock) => [
                    'label' => $stock->medicine?->name ?? 'Unknown medicine',
                    'total' => (float) $stock->total,
                ])->values(),
        ];
    }

    public function laboratory(array $range): array
    {
        [$from, $to] = $this->range($range);
        $requests = LabRequest::query()->whereBetween('requested_at', [$from->startOfDay(), $to->endOfDay()]);

        return [
            'period' => $this->period($from, $to),
            'total_requests' => $requests->count(),
            'completed_requests' => (clone $requests)->where('status', 'COMPLETED')->count(),
            'by_status' => $this->keyValueCounts((clone $requests)->selectRaw('status as label, COUNT(*) as total')->groupBy('status')->get()),
            'requests_by_date' => $this->dailyCounts(LabRequest::query(), 'requested_at', $from, $to),
            'top_tests' => LabRequest::query()
                ->with('labTest:id,name')
                ->whereBetween('requested_at', [$from->startOfDay(), $to->endOfDay()])
                ->selectRaw('lab_test_id, COUNT(*) as total')
                ->groupBy('lab_test_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(fn (LabRequest $request) => [
                    'label' => $request->labTest?->name ?? 'Unknown test',
                    'total' => (int) $request->total,
                ])->values(),
        ];
    }

    public function staff(array $range): array
    {
        [$from, $to] = $this->range($range);
        $attendance = AttendanceRecord::query()->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()]);
        $leaves = LeaveRequest::query()->whereBetween('requested_at', [$from->startOfDay(), $to->endOfDay()]);

        return [
            'period' => $this->period($from, $to),
            'active_employees' => Employee::query()->where('status', 'ACTIVE')->count(),
            'attendance_records' => $attendance->count(),
            'attendance_by_status' => $this->keyValueCounts((clone $attendance)->selectRaw('status as label, COUNT(*) as total')->groupBy('status')->get()),
            'attendance_by_date' => $this->dailyCounts(AttendanceRecord::query(), 'attendance_date', $from, $to),
            'leave_by_status' => $this->keyValueCounts((clone $leaves)->selectRaw('status as label, COUNT(*) as total')->groupBy('status')->get()),
            'leave_days_approved' => (int) LeaveRequest::query()->where('status', 'APPROVED')->whereDate('start_date', '<=', $to)->whereDate('end_date', '>=', $from)->sum('total_days'),
        ];
    }

    private function range(array $range): array
    {
        $from = Carbon::parse($range['from_date'] ?? today()->startOfMonth())->startOfDay();
        $to = Carbon::parse($range['to_date'] ?? today())->startOfDay();
        if ($from->greaterThan($to)) {
            throw ValidationException::withMessages(['from_date' => ['The start date must be before or on the end date.']]);
        }
        if ($from->diffInDays($to) > 366) {
            throw ValidationException::withMessages(['to_date' => ['Reports are limited to a maximum range of 366 days.']]);
        }

        return [$from, $to];
    }

    private function period(Carbon $from, Carbon $to): array
    {
        return ['from_date' => $from->toDateString(), 'to_date' => $to->toDateString()];
    }

    private function keyValueCounts($rows): array
    {
        return $rows->map(fn ($row) => ['label' => $row->label ?? 'Unspecified', 'total' => (int) $row->total])->values()->all();
    }

    private function keyValueSums($rows): array
    {
        return $rows->map(fn ($row) => ['label' => $row->label ?? 'Unspecified', 'total' => $this->decimal($row->total)])->values()->all();
    }

    private function dailyCounts($query, string $column, Carbon $from, Carbon $to): array
    {
        $rows = $query->whereBetween($column, [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw("DATE({$column}) as report_date, COUNT(*) as total")
            ->groupBy('report_date')
            ->pluck('total', 'report_date');

        return $this->dateSeries($rows, $from, $to, false);
    }

    private function dailySums($query, string $column, string $amountColumn, Carbon $from, Carbon $to): array
    {
        $rows = $query->whereBetween($column, [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw("DATE({$column}) as report_date, SUM({$amountColumn}) as total")
            ->groupBy('report_date')
            ->pluck('total', 'report_date');

        return $this->dateSeries($rows, $from, $to, true);
    }

    private function dateSeries($rows, Carbon $from, Carbon $to, bool $decimal): array
    {
        $series = [];
        for ($date = $from->copy(); $date->lessThanOrEqualTo($to); $date->addDay()) {
            $value = $rows[$date->toDateString()] ?? 0;
            $series[] = [
                'label' => $date->toDateString(),
                'total' => $decimal ? $this->decimal($value) : (int) $value,
            ];
        }

        return $series;
    }

    private function decimal($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
