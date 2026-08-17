<?php

namespace App\Http\Resources;

use App\Enums\InvoiceStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_code' => $this->invoice_code,
            'status' => $this->status instanceof InvoiceStatus ? $this->status->value : $this->status,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'balance' => $this->balance,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'due_date' => $this->due_date?->toDateString(),
            'notes' => $this->notes,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient?->id,
                'patient_code' => $this->patient?->patient_code,
                'full_name' => trim(($this->patient?->first_name ?? '').' '.($this->patient?->last_name ?? '')),
                'phone' => $this->patient?->phone,
                'address_line_1' => $this->patient?->address_line_1,
                'city' => $this->patient?->city,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'description' => $item->description,
                'source_type' => $item->source_type,
                'source_id' => $item->source_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->values()),
            'payments' => $this->whenLoaded('payments', fn () => $this->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'payment_code' => $payment->payment_code,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'notes' => $payment->notes,
                'received_by' => $payment->relationLoaded('receivedBy') ? $payment->receivedBy?->name : null,
            ])->values()),
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->name),
            'cancelled_by' => $this->whenLoaded('cancelledBy', fn () => $this->cancelledBy?->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
