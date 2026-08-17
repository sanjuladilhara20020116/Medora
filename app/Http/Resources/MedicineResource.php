<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medicine_code' => $this->medicine_code,
            'name' => $this->name,
            'generic_name' => $this->generic_name,
            'manufacturer' => $this->manufacturer,
            'dosage_form' => $this->dosage_form,
            'strength' => $this->strength,
            'reorder_level' => $this->reorder_level,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'stock_total' => $this->stock_total !== null ? (string) $this->stock_total : '0.00',
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'category_code' => $this->category?->category_code,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
