<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $daysToExpiry = $this->expiry_date ? now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false) : null;

        return [
            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'received_date' => $this->received_date?->toDateString(),
            'quantity_received' => $this->quantity_received,
            'quantity_available' => $this->quantity_available,
            'unit_cost' => $this->unit_cost,
            'selling_price' => $this->selling_price,
            'supplier' => $this->supplier,
            'notes' => $this->notes,
            'days_to_expiry' => $daysToExpiry,
            'is_expired' => $daysToExpiry !== null && $daysToExpiry < 0,
            'expires_soon' => $daysToExpiry !== null && $daysToExpiry >= 0 && $daysToExpiry <= 30,
            'medicine' => $this->whenLoaded('medicine', fn () => [
                'id' => $this->medicine?->id,
                'medicine_code' => $this->medicine?->medicine_code,
                'name' => $this->medicine?->name,
                'dosage_form' => $this->medicine?->dosage_form,
                'strength' => $this->medicine?->strength,
            ]),
            'received_by' => $this->whenLoaded('receivedBy', fn () => $this->receivedBy?->name),
        ];
    }
}
