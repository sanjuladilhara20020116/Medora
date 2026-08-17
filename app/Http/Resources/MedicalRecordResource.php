<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'record_code' => $this->record_code,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
            'chief_complaint' => $this->chief_complaint,
            'diagnosis' => $this->diagnosis,
            'treatment_plan' => $this->treatment_plan,
            'clinical_notes' => $this->clinical_notes,
            'follow_up_date' => $this->follow_up_date?->toDateString(),
            'follow_up_notes' => $this->follow_up_notes,
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient?->id,
                'patient_code' => $this->patient?->patient_code,
                'full_name' => trim(($this->patient?->first_name ?? '').' '.($this->patient?->last_name ?? '')),
                'date_of_birth' => $this->patient?->date_of_birth?->toDateString(),
                'age' => $this->patient?->date_of_birth?->age,
                'gender' => $this->patient?->gender,
                'blood_group' => $this->patient?->blood_group,
                'allergies' => $this->patient?->allergies,
                'chronic_conditions' => $this->patient?->chronic_conditions,
                'phone' => $this->patient?->phone,
            ]),
            'doctor' => $this->whenLoaded('doctor', fn () => [
                'id' => $this->doctor?->id,
                'doctor_code' => $this->doctor?->doctor_code,
                'name' => $this->doctor?->user?->name,
                'specialization' => $this->doctor?->specialization,
            ]),
            'appointment' => $this->whenLoaded('appointment', fn () => [
                'id' => $this->appointment?->id,
                'appointment_code' => $this->appointment?->appointment_code,
                'appointment_date' => $this->appointment?->appointment_date?->toDateString(),
                'start_time' => $this->appointment ? substr($this->appointment->start_time, 0, 5) : null,
                'status' => $this->appointment?->status,
            ]),
            'prescription' => $this->whenLoaded('prescription', fn () => $this->prescription ? [
                'id' => $this->prescription->id,
                'prescription_code' => $this->prescription->prescription_code,
                'issued_at' => $this->prescription->issued_at?->toIso8601String(),
                'notes' => $this->prescription->notes,
                'items' => $this->prescription->relationLoaded('items')
                    ? $this->prescription->items->map(fn ($item) => [
                        'id' => $item->id,
                        'medicine_name' => $item->medicine_name,
                        'dosage' => $item->dosage,
                        'frequency' => $item->frequency,
                        'duration_days' => $item->duration_days,
                        'quantity' => $item->quantity,
                        'instructions' => $item->instructions,
                    ])->values()
                    : [],
            ] : null),
            'reports' => $this->whenLoaded('reports', fn () => $this->reports->map(fn ($report) => [
                'id' => $report->id,
                'report_type' => $report->report_type,
                'title' => $report->title,
                'file_name' => $report->file_name,
                'mime_type' => $report->mime_type,
                'file_size' => $report->file_size,
                'notes' => $report->notes,
                'created_at' => $report->created_at?->toIso8601String(),
                'download_endpoint' => "/medical-records/{$this->id}/reports/{$report->id}/download",
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
