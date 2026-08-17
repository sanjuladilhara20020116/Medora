<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_code' => $this->request_code,
            'priority' => $this->priority,
            'status' => $this->status,
            'clinical_notes' => $this->clinical_notes,
            'requested_at' => $this->requested_at?->toIso8601String(),
            'sample_collected_at' => $this->sample_collected_at?->toIso8601String(),
            'sample_identifier' => $this->sample_identifier,
            'specimen_condition' => $this->specimen_condition,
            'sample_notes' => $this->sample_notes,
            'processing_started_at' => $this->processing_started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient?->id,
                'patient_code' => $this->patient?->patient_code,
                'full_name' => trim(($this->patient?->first_name ?? '').' '.($this->patient?->last_name ?? '')),
                'date_of_birth' => $this->patient?->date_of_birth?->toDateString(),
                'age' => $this->patient?->date_of_birth?->age,
                'gender' => $this->patient?->gender,
                'blood_group' => $this->patient?->blood_group,
                'phone' => $this->patient?->phone,
            ]),
            'doctor' => $this->whenLoaded('doctor', fn () => [
                'id' => $this->doctor?->id,
                'doctor_code' => $this->doctor?->doctor_code,
                'name' => $this->doctor?->user?->name,
                'specialization' => $this->doctor?->specialization,
            ]),
            'lab_test' => $this->whenLoaded('labTest', fn () => [
                'id' => $this->labTest?->id,
                'test_code' => $this->labTest?->test_code,
                'name' => $this->labTest?->name,
                'category' => $this->labTest?->category,
                'specimen_type' => $this->labTest?->specimen_type,
                'unit' => $this->labTest?->unit,
                'reference_range' => $this->labTest?->reference_range,
                'turnaround_hours' => $this->labTest?->turnaround_hours,
            ]),
            'medical_record' => $this->whenLoaded('medicalRecord', fn () => [
                'id' => $this->medicalRecord?->id,
                'record_code' => $this->medicalRecord?->record_code,
                'diagnosis' => $this->medicalRecord?->diagnosis,
            ]),
            'requested_by' => $this->whenLoaded('requestedBy', fn () => [
                'id' => $this->requestedBy?->id,
                'name' => $this->requestedBy?->name,
            ]),
            'sample_collected_by' => $this->whenLoaded('sampleCollectedBy', fn () => [
                'id' => $this->sampleCollectedBy?->id,
                'name' => $this->sampleCollectedBy?->name,
            ]),
            'result' => $this->whenLoaded('result', fn () => $this->result ? [
                'id' => $this->result->id,
                'result_value' => $this->result->result_value,
                'unit' => $this->result->unit,
                'reference_range' => $this->result->reference_range,
                'interpretation' => $this->result->interpretation,
                'remarks' => $this->result->remarks,
                'resulted_at' => $this->result->resulted_at?->toIso8601String(),
                'entered_by' => $this->result->relationLoaded('enteredBy') ? $this->result->enteredBy?->name : null,
                'verified_by' => $this->result->relationLoaded('verifiedBy') ? $this->result->verifiedBy?->name : null,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
