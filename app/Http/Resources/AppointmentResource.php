<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'appointment_code' =>
                $this->appointment_code,

            'appointment_date' =>
                $this->appointment_date
                    ?->toDateString(),

            'start_time' =>
                substr($this->start_time, 0, 5),

            'end_time' =>
                substr($this->end_time, 0, 5),

            'appointment_type' =>
                $this->appointment_type,

            'priority' =>
                $this->priority,

            'status' =>
                $this->status,

            'reason' =>
                $this->reason,

            'notes' =>
                $this->notes,

            'patient' =>
                $this->whenLoaded(
                    'patient',
                    fn () => [
                        'id' =>
                            $this->patient?->id,

                        'patient_code' =>
                            $this->patient
                                ?->patient_code,

                        'full_name' =>
                            trim(
                                ($this->patient
                                    ?->first_name ?? '')
                                . ' '
                                . ($this->patient
                                    ?->last_name ?? '')
                            ),

                        'phone' =>
                            $this->patient?->phone,
                    ]
                ),

            'doctor' =>
                $this->whenLoaded(
                    'doctor',
                    fn () => [
                        'id' =>
                            $this->doctor?->id,

                        'doctor_code' =>
                            $this->doctor
                                ?->doctor_code,

                        'name' =>
                            $this->doctor
                                ?->user
                                ?->name,

                        'specialization' =>
                            $this->doctor
                                ?->specialization,
                    ]
                ),

            'department' =>
                $this->whenLoaded(
                    'department',
                    fn () => [
                        'id' =>
                            $this->department?->id,

                        'code' =>
                            $this->department?->code,

                        'name' =>
                            $this->department?->name,
                    ]
                ),

            'created_by' =>
                $this->whenLoaded(
                    'createdBy',
                    fn () => [
                        'id' =>
                            $this->createdBy?->id,

                        'name' =>
                            $this->createdBy?->name,
                    ]
                ),

            'cancelled_by' =>
                $this->whenLoaded(
                    'cancelledBy',
                    fn () => [
                        'id' =>
                            $this->cancelledBy?->id,

                        'name' =>
                            $this->cancelledBy?->name,
                    ]
                ),

            'checked_in_at' =>
                $this->checked_in_at
                    ?->toIso8601String(),

            'started_at' =>
                $this->started_at
                    ?->toIso8601String(),

            'completed_at' =>
                $this->completed_at
                    ?->toIso8601String(),

            'cancelled_at' =>
                $this->cancelled_at
                    ?->toIso8601String(),

            'cancellation_reason' =>
                $this->cancellation_reason,

            'created_at' =>
                $this->created_at
                    ?->toIso8601String(),
        ];
    }
}