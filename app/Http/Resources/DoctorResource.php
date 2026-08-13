<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'doctor_code' =>
                $this->doctor_code,

            'registration_number' =>
                $this->registration_number,

            'designation' =>
                $this->designation,

            'specialization' =>
                $this->specialization,

            'qualifications' =>
                $this->qualifications,

            'experience_years' =>
                $this->experience_years,

            'consultation_fee' =>
                $this->consultation_fee,

            'room_number' =>
                $this->room_number,

            'biography' =>
                $this->biography,

            'joined_at' =>
                $this->joined_at?->toDateString(),

            'is_available' =>
                $this->is_available,

            'is_active' =>
                $this->is_active,

            'user' =>
                $this->whenLoaded(
                    'user',
                    fn () => [
                        'id' =>
                            $this->user?->id,

                        'name' =>
                            $this->user?->name,

                        'username' =>
                            $this->user?->username,

                        'email' =>
                            $this->user?->email,

                        'phone' =>
                            $this->user?->phone,
                    ]
                ),

            'departments' =>
                $this->whenLoaded(
                    'departments',
                    fn () =>
                        $this->departments
                            ->map(
                                fn ($department) => [
                                    'id' =>
                                        $department->id,

                                    'code' =>
                                        $department->code,

                                    'name' =>
                                        $department->name,

                                    'is_primary' =>
                                        (bool)
                                        $department
                                            ->pivot
                                            ->is_primary,
                                ]
                            )
                ),

            'schedules' =>
                $this->whenLoaded(
                    'schedules',
                    fn () =>
                        $this->schedules
                            ->map(
                                fn ($schedule) => [
                                    'id' =>
                                        $schedule->id,

                                    'department_id' =>
                                        $schedule
                                            ->department_id,

                                    'department' =>
                                        $schedule
                                            ->department
                                            ?->name,

                                    'day_of_week' =>
                                        $schedule
                                            ->day_of_week,

                                    'start_time' =>
                                        substr(
                                            $schedule
                                                ->start_time,
                                            0,
                                            5
                                        ),

                                    'end_time' =>
                                        substr(
                                            $schedule
                                                ->end_time,
                                            0,
                                            5
                                        ),

                                    'slot_duration_minutes' =>
                                        $schedule
                                            ->slot_duration_minutes,

                                    'max_appointments' =>
                                        $schedule
                                            ->max_appointments,

                                    'is_active' =>
                                        $schedule
                                            ->is_active,
                                ]
                            )
                ),

            'created_at' =>
                $this->created_at?->toIso8601String(),
        ];
    }
}