<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabTestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'test_code' => $this->test_code,
            'name' => $this->name,
            'category' => $this->category,
            'specimen_type' => $this->specimen_type,
            'unit' => $this->unit,
            'reference_range' => $this->reference_range,
            'turnaround_hours' => $this->turnaround_hours,
            'price' => $this->price,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
