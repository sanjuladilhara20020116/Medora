<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\InvoiceStatus;
use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PrescriptionDispense;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingService
{
    public function invoices(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);
        $search = trim((string) ($filters['search'] ?? ''));

        return Invoice::query()
            ->with(['patient:id,patient_code,first_name,last_name,phone', 'items', 'payments'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('invoice_code', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($query) use ($search) {
                            $query->where('patient_code', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['patient_id']), fn ($query) => $query->where('patient_id', $filters['patient_id']))
            ->orderByDesc('issued_at')
            ->paginate($perPage);
    }

    public function summary(): array
    {
        $invoices = Invoice::query()->where('status', '!=', InvoiceStatus::CANCELLED->value);

        return [
            'invoice_count' => (clone $invoices)->count(),
            'unpaid_count' => (clone $invoices)->where('status', InvoiceStatus::UNPAID->value)->count(),
            'total_invoiced' => number_format((float) (clone $invoices)->sum('total_amount'), 2, '.', ''),
            'total_paid' => number_format((float) (clone $invoices)->sum('paid_amount'), 2, '.', ''),
            'total_outstanding' => number_format((float) (clone $invoices)->sum('balance'), 2, '.', ''),
        ];
    }

    public function patients(string $search = ''): array
    {
        return Patient::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('patient_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(50)
            ->get(['id', 'patient_code', 'first_name', 'last_name', 'phone'])
            ->map(fn (Patient $patient) => [
                'id' => $patient->id,
                'patient_code' => $patient->patient_code,
                'full_name' => trim($patient->first_name.' '.$patient->last_name),
                'phone' => $patient->phone,
            ])
            ->values()
            ->all();
    }

    public function availableCharges(int $patientId): array
    {
        $this->ensurePatientExists($patientId);

        $items = [];

        Appointment::query()
            ->with('doctor.user:id,name')
            ->where('patient_id', $patientId)
            ->where('status', AppointmentStatus::COMPLETED->value)
            ->orderByDesc('appointment_date')
            ->get()
            ->filter(fn (Appointment $appointment) => ! $this->sourceAlreadyInActiveInvoice('APPOINTMENT', $appointment->id))
            ->each(function (Appointment $appointment) use (&$items) {
                $fee = (float) ($appointment->doctor?->consultation_fee ?? 0);
                $items[] = $this->sourceItem(
                    'APPOINTMENT',
                    $appointment->id,
                    'CONSULTATION',
                    sprintf('Consultation · %s · Dr. %s', $appointment->appointment_date?->format('d M Y'), $appointment->doctor?->user?->name ?? '—'),
                    1,
                    $fee,
                    $appointment->appointment_code,
                );
            });

        LabRequest::query()
            ->with('labTest:id,test_code,name,price')
            ->where('patient_id', $patientId)
            ->where('status', '!=', 'CANCELLED')
            ->orderByDesc('requested_at')
            ->get()
            ->filter(fn (LabRequest $request) => ! $this->sourceAlreadyInActiveInvoice('LAB_REQUEST', $request->id))
            ->each(function (LabRequest $request) use (&$items) {
                $items[] = $this->sourceItem(
                    'LAB_REQUEST',
                    $request->id,
                    'LABORATORY',
                    'Laboratory test · '.($request->labTest?->name ?? '—'),
                    1,
                    (float) ($request->labTest?->price ?? 0),
                    $request->request_code,
                );
            });

        PrescriptionDispense::query()
            ->with('items')
            ->where('patient_id', $patientId)
            ->orderByDesc('dispensed_at')
            ->get()
            ->filter(fn (PrescriptionDispense $dispense) => ! $this->sourceAlreadyInActiveInvoice('PRESCRIPTION_DISPENSE', $dispense->id))
            ->each(function (PrescriptionDispense $dispense) use (&$items) {
                $total = $dispense->items->sum(fn ($item) => (float) $item->quantity_dispensed * (float) $item->unit_price);
                $items[] = $this->sourceItem(
                    'PRESCRIPTION_DISPENSE',
                    $dispense->id,
                    'PHARMACY',
                    'Pharmacy dispensing · '.$dispense->dispense_code,
                    1,
                    $total,
                    $dispense->dispense_code,
                );
            });

        Admission::query()
            ->where('patient_id', $patientId)
            ->whereNotNull('admitted_on')
            ->orderByDesc('admitted_on')
            ->get()
            ->filter(fn (Admission $admission) => ! $this->sourceAlreadyInActiveInvoice('ADMISSION', $admission->id))
            ->each(function (Admission $admission) use (&$items) {
                $days = max(1, $admission->admitted_on->diffInDays($admission->discharged_on ?? today()) + 1);
                $items[] = $this->sourceItem(
                    'ADMISSION',
                    $admission->id,
                    'ADMISSION',
                    'Admission'.($admission->room_number ? ' · Room '.$admission->room_number : ''),
                    $days,
                    (float) $admission->daily_rate,
                    $admission->admission_code,
                );
            });

        return $items;
    }

    public function createInvoice(array $data, User $user): Invoice
    {
        return DB::transaction(function () use ($data, $user) {
            $patientId = (int) $data['patient_id'];
            $this->ensurePatientExists($patientId);
            $items = [];
            $sourceKeys = [];

            foreach ($data['charge_sources'] ?? [] as $source) {
                $key = $source['type'].':'.$source['id'];
                if (in_array($key, $sourceKeys, true)) {
                    throw ValidationException::withMessages(['charge_sources' => ['Each charge can only be selected once.']]);
                }
                $sourceKeys[] = $key;
                $items[] = $this->resolveSource($source['type'], (int) $source['id'], $patientId, true);
            }

            foreach ($data['manual_items'] ?? [] as $item) {
                $quantity = round((float) $item['quantity'], 2);
                $unitPrice = round((float) $item['unit_price'], 2);
                $items[] = [
                    'item_type' => $item['item_type'],
                    'description' => trim($item['description']),
                    'source_type' => null,
                    'source_id' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => round($quantity * $unitPrice, 2),
                ];
            }

            if ($items === []) {
                throw ValidationException::withMessages([
                    'charge_sources' => ['Select at least one charge or add an admission/other line.'],
                ]);
            }

            $subtotal = round(array_sum(array_column($items, 'line_total')), 2);
            $discount = round((float) ($data['discount_amount'] ?? 0), 2);
            $tax = round((float) ($data['tax_amount'] ?? 0), 2);
            if ($discount > $subtotal) {
                throw ValidationException::withMessages(['discount_amount' => ['The discount cannot exceed the subtotal.']]);
            }

            $total = round($subtotal - $discount + $tax, 2);
            $invoice = Invoice::create([
                'invoice_code' => null,
                'patient_id' => $patientId,
                'status' => InvoiceStatus::UNPAID,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'paid_amount' => 0,
                'balance' => $total,
                'issued_at' => now(),
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);
            $invoice->update(['invoice_code' => sprintf('INV-%s-%05d', now()->format('Y'), $invoice->id)]);

            $invoice->items()->createMany($items);

            return $this->loadInvoice($invoice);
        });
    }

    public function loadInvoice(Invoice $invoice): Invoice
    {
        return $invoice->load([
            'patient:id,patient_code,first_name,last_name,phone,address_line_1,city',
            'items',
            'payments.receivedBy:id,name',
            'createdBy:id,name',
            'cancelledBy:id,name',
        ]);
    }

    public function recordPayment(Invoice $invoice, array $data, User $user): Invoice
    {
        DB::transaction(function () use ($invoice, $data, $user) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if ($invoice->status === InvoiceStatus::CANCELLED) {
                throw ValidationException::withMessages(['invoice' => ['A cancelled invoice cannot receive a payment.']]);
            }

            $amount = round((float) $data['amount'], 2);
            $balance = round((float) $invoice->balance, 2);
            if ($amount > $balance) {
                throw ValidationException::withMessages(['amount' => ['The payment cannot exceed the outstanding balance of '.number_format($balance, 2).'.']]);
            }

            $payment = Payment::create([
                'payment_code' => null,
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'received_by' => $user->id,
            ]);
            $payment->update(['payment_code' => sprintf('PAY-%s-%05d', now()->format('Y'), $payment->id)]);

            $paid = round((float) $invoice->paid_amount + $amount, 2);
            $remaining = max(0, round((float) $invoice->total_amount - $paid, 2));
            $invoice->update([
                'paid_amount' => $paid,
                'balance' => $remaining,
                'status' => $remaining <= 0 ? InvoiceStatus::PAID : InvoiceStatus::PARTIALLY_PAID,
            ]);
        });

        return $this->loadInvoice($invoice->fresh());
    }

    public function cancelInvoice(Invoice $invoice, User $user): Invoice
    {
        if ($invoice->status !== InvoiceStatus::UNPAID) {
            throw ValidationException::withMessages([
                'invoice' => ['Only an unpaid invoice can be cancelled. Payments must be handled before cancelling an invoice.'],
            ]);
        }

        $invoice->update([
            'status' => InvoiceStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $user->id,
        ]);

        return $this->loadInvoice($invoice->fresh());
    }

    private function resolveSource(string $type, int $sourceId, int $patientId, bool $preventDuplicate = false): array
    {
        if ($preventDuplicate && $this->sourceAlreadyInActiveInvoice($type, $sourceId)) {
            throw ValidationException::withMessages(['charge_sources' => ['One or more selected charges have already been invoiced. Refresh the available charges and try again.']]);
        }

        return match ($type) {
            'APPOINTMENT' => $this->resolveAppointment($sourceId, $patientId),
            'LAB_REQUEST' => $this->resolveLabRequest($sourceId, $patientId),
            'PRESCRIPTION_DISPENSE' => $this->resolveDispense($sourceId, $patientId),
            'ADMISSION' => $this->resolveAdmission($sourceId, $patientId),
        };
    }

    private function resolveAppointment(int $sourceId, int $patientId): array
    {
        $appointment = Appointment::query()->with('doctor.user:id,name')->find($sourceId);
        if (! $appointment || $appointment->patient_id !== $patientId || $appointment->status !== AppointmentStatus::COMPLETED->value) {
            throw ValidationException::withMessages(['charge_sources' => ['The selected consultation is not available for this patient.']]);
        }

        return $this->sourceItem('APPOINTMENT', $appointment->id, 'CONSULTATION', sprintf('Consultation · %s · Dr. %s', $appointment->appointment_date?->format('d M Y'), $appointment->doctor?->user?->name ?? '—'), 1, (float) ($appointment->doctor?->consultation_fee ?? 0), $appointment->appointment_code);
    }

    private function resolveLabRequest(int $sourceId, int $patientId): array
    {
        $request = LabRequest::query()->with('labTest:id,test_code,name,price')->find($sourceId);
        if (! $request || $request->patient_id !== $patientId || $request->status === 'CANCELLED') {
            throw ValidationException::withMessages(['charge_sources' => ['The selected laboratory request is not available for this patient.']]);
        }

        return $this->sourceItem('LAB_REQUEST', $request->id, 'LABORATORY', 'Laboratory test · '.($request->labTest?->name ?? '—'), 1, (float) ($request->labTest?->price ?? 0), $request->request_code);
    }

    private function resolveDispense(int $sourceId, int $patientId): array
    {
        $dispense = PrescriptionDispense::query()->with('items')->find($sourceId);
        if (! $dispense || $dispense->patient_id !== $patientId) {
            throw ValidationException::withMessages(['charge_sources' => ['The selected pharmacy dispensing is not available for this patient.']]);
        }

        $total = $dispense->items->sum(fn ($item) => (float) $item->quantity_dispensed * (float) $item->unit_price);

        return $this->sourceItem('PRESCRIPTION_DISPENSE', $dispense->id, 'PHARMACY', 'Pharmacy dispensing · '.$dispense->dispense_code, 1, $total, $dispense->dispense_code);
    }

    private function resolveAdmission(int $sourceId, int $patientId): array
    {
        $admission = Admission::query()->find($sourceId);
        if (! $admission || $admission->patient_id !== $patientId || ! $admission->admitted_on) {
            throw ValidationException::withMessages(['charge_sources' => ['The selected admission is not available for this patient.']]);
        }

        $days = max(1, $admission->admitted_on->diffInDays($admission->discharged_on ?? today()) + 1);

        return $this->sourceItem('ADMISSION', $admission->id, 'ADMISSION', 'Admission'.($admission->room_number ? ' · Room '.$admission->room_number : ''), $days, (float) $admission->daily_rate, $admission->admission_code);
    }

    private function sourceItem(string $sourceType, int $sourceId, string $itemType, string $description, float|int $quantity, float $unitPrice, ?string $reference): array
    {
        $quantity = round((float) $quantity, 2);
        $unitPrice = round($unitPrice, 2);

        return [
            'item_type' => $itemType,
            'description' => $description,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($quantity * $unitPrice, 2),
            'reference' => $reference,
        ];
    }

    private function sourceAlreadyInActiveInvoice(string $type, int $id): bool
    {
        return InvoiceItem::query()
            ->where('source_type', $type)
            ->where('source_id', $id)
            ->whereHas('invoice', fn ($query) => $query->where('status', '!=', InvoiceStatus::CANCELLED->value))
            ->exists();
    }

    private function ensurePatientExists(int $patientId): void
    {
        if (! Patient::query()->whereKey($patientId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['patient_id' => ['The selected patient is not active.']]);
        }
    }
}
