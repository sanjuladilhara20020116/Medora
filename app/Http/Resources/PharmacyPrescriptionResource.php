<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyPrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items', fn () => $this->items->map(function ($item) {
            $dispensedQuantity = $item->relationLoaded('dispenseItems')
                ? (float) $item->dispenseItems->sum('quantity_dispensed')
                : 0;
            $prescribedQuantity = $item->quantity !== null ? (float) $item->quantity : null;

            return [
                'id' => $item->id,
                'medicine_name' => $item->medicine_name,
                'dosage' => $item->dosage,
                'frequency' => $item->frequency,
                'duration_days' => $item->duration_days,
                'quantity' => $item->quantity,
                'instructions' => $item->instructions,
                'dispensed_quantity' => number_format($dispensedQuantity, 2, '.', ''),
                'remaining_quantity' => $prescribedQuantity !== null
                    ? number_format(max(0, $prescribedQuantity - $dispensedQuantity), 2, '.', '')
                    : null,
            ];
        })->values());

        $allItemsComplete = $this->relationLoaded('items')
            && $this->items->isNotEmpty()
            && $this->items->every(function ($item) {
                if ($item->quantity === null) {
                    return false;
                }

                return (float) $item->dispenseItems->sum('quantity_dispensed') >= (float) $item->quantity;
            });

        return [
            'id' => $this->id,
            'prescription_code' => $this->prescription_code,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'notes' => $this->notes,
            'dispense_status' => $allItemsComplete ? 'COMPLETED' : ($this->dispenses?->isNotEmpty() ? 'PARTIAL' : 'PENDING'),
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient?->id,
                'patient_code' => $this->patient?->patient_code,
                'full_name' => trim(($this->patient?->first_name ?? '').' '.($this->patient?->last_name ?? '')),
                'allergies' => $this->patient?->allergies,
            ]),
            'doctor' => $this->whenLoaded('prescribedBy', fn () => [
                'id' => $this->prescribedBy?->id,
                'name' => $this->prescribedBy?->user?->name,
            ]),
            'items' => $items,
            'dispenses' => $this->whenLoaded('dispenses', fn () => $this->dispenses->map(fn ($dispense) => [
                'id' => $dispense->id,
                'dispense_code' => $dispense->dispense_code,
                'status' => $dispense->status,
                'notes' => $dispense->notes,
                'dispensed_at' => $dispense->dispensed_at?->toIso8601String(),
                'dispensed_by' => $dispense->relationLoaded('dispensedBy') ? $dispense->dispensedBy?->name : null,
            ])->values()),
        ];
    }
}
