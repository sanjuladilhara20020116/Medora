<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    private const BLOCKING_STATUSES = [
        'SCHEDULED',
        'CHECKED_IN',
        'IN_PROGRESS',
    ];


    public function paginate(
        array $filters,
        User $user
    ): LengthAwarePaginator {

        $search = trim(
            (string) ($filters['search'] ?? '')
        );

        $perPage = min(
            max(
                (int) ($filters['per_page'] ?? 10),
                5
            ),
            50
        );


        $query = Appointment::query()
            ->with([
                'patient:id,patient_code,first_name,last_name,phone',

                'doctor:id,user_id,doctor_code,specialization',

                'doctor.user:id,name',

                'department:id,code,name',
            ]);


        if ($user->role?->slug === 'DOCTOR') {

            $doctorId =
                $user->doctor?->id;

            if (! $doctorId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(
                    'doctor_id',
                    $doctorId
                );
            }
        }


        $query
            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        function ($query) use ($search) {

                            $query
                                ->where(
                                    'appointment_code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhereHas(
                                    'patient',
                                    function ($query)
                                    use ($search) {

                                        $query
                                            ->where(
                                                'patient_code',
                                                'like',
                                                "%{$search}%"
                                            )

                                            ->orWhere(
                                                'first_name',
                                                'like',
                                                "%{$search}%"
                                            )

                                            ->orWhere(
                                                'last_name',
                                                'like',
                                                "%{$search}%"
                                            );
                                    }
                                )

                                ->orWhereHas(
                                    'doctor.user',
                                    fn ($query) =>
                                        $query->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                );
                        }
                    );
                }
            )

            ->when(
                ! empty($filters['status']),
                fn ($query) =>
                    $query->where(
                        'status',
                        $filters['status']
                    )
            )

            ->when(
                ! empty($filters['appointment_date']),
                fn ($query) =>
                    $query->whereDate(
                        'appointment_date',
                        $filters['appointment_date']
                    )
            )

            ->when(
                ! empty($filters['doctor_id']),
                fn ($query) =>
                    $query->where(
                        'doctor_id',
                        $filters['doctor_id']
                    )
            )

            ->when(
                ! empty($filters['department_id']),
                fn ($query) =>
                    $query->where(
                        'department_id',
                        $filters['department_id']
                    )
            )

            ->when(
                ! empty($filters['patient_id']),
                fn ($query) =>
                    $query->where(
                        'patient_id',
                        $filters['patient_id']
                    )
            );


        return $query
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate($perPage);
    }


    public function availability(
        int $doctorId,
        int $departmentId,
        string $date
    ): array {

        $this->validateDoctorDepartment(
            $doctorId,
            $departmentId
        );


        $appointmentDate =
            CarbonImmutable::parse($date);

        $day =
            strtoupper(
                $appointmentDate
                    ->format('l')
            );


        $schedules =
            DoctorSchedule::query()
                ->where(
                    'doctor_id',
                    $doctorId
                )
                ->where(
                    'department_id',
                    $departmentId
                )
                ->where(
                    'day_of_week',
                    $day
                )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('start_time')
                ->get();


        $appointments =
            Appointment::query()
                ->where(
                    'doctor_id',
                    $doctorId
                )
                ->whereDate(
                    'appointment_date',
                    $date
                )
                ->whereIn(
                    'status',
                    self::BLOCKING_STATUSES
                )
                ->get([
                    'start_time',
                    'end_time',
                ]);


        $slots = [];


        foreach ($schedules as $schedule) {

            $appointmentCount =
                Appointment::query()
                    ->where(
                        'doctor_schedule_id',
                        $schedule->id
                    )
                    ->whereDate(
                        'appointment_date',
                        $date
                    )
                    ->whereIn(
                        'status',
                        self::BLOCKING_STATUSES
                    )
                    ->count();


            if (
                $schedule->max_appointments
                && $appointmentCount
                    >= $schedule->max_appointments
            ) {
                continue;
            }


            $cursor =
                CarbonImmutable::parse(
                    $date
                    . ' '
                    . $schedule->start_time
                );


            $scheduleEnd =
                CarbonImmutable::parse(
                    $date
                    . ' '
                    . $schedule->end_time
                );


            $duration =
                (int)
                $schedule
                    ->slot_duration_minutes;


            while (true) {

                $slotEnd =
                    $cursor->addMinutes(
                        $duration
                    );


                if (
                    $slotEnd->greaterThan(
                        $scheduleEnd
                    )
                ) {
                    break;
                }


                if (
                    $appointmentDate->isToday()
                    && $cursor
                        ->lessThanOrEqualTo(now())
                ) {
                    $cursor = $slotEnd;
                    continue;
                }


                $busy =
                    $appointments
                        ->contains(
                            function (
                                Appointment $appointment
                            ) use (
                                $date,
                                $cursor,
                                $slotEnd
                            ) {

                                $existingStart =
                                    CarbonImmutable::parse(
                                        $date
                                        . ' '
                                        . $appointment
                                            ->start_time
                                    );


                                $existingEnd =
                                    CarbonImmutable::parse(
                                        $date
                                        . ' '
                                        . $appointment
                                            ->end_time
                                    );


                                return
                                    $cursor
                                        ->lessThan(
                                            $existingEnd
                                        )
                                    &&
                                    $slotEnd
                                        ->greaterThan(
                                            $existingStart
                                        );
                            }
                        );


                if (! $busy) {

                    $slots[] = [
                        'schedule_id' =>
                            $schedule->id,

                        'start_time' =>
                            $cursor
                                ->format('H:i'),

                        'end_time' =>
                            $slotEnd
                                ->format('H:i'),

                        'label' =>
                            $cursor
                                ->format('H:i')
                            . ' - '
                            . $slotEnd
                                ->format('H:i'),
                    ];
                }


                $cursor = $slotEnd;
            }
        }


        return $slots;
    }


    public function create(
        array $data,
        int $createdBy
    ): Appointment {

        return DB::transaction(
            function () use (
                $data,
                $createdBy
            ) {

                $slot =
                    $this
                        ->resolveSlot(
                            $data
                        );


                $this
                    ->ensureSlotAvailable(
                        $data[
                            'patient_id'
                        ],
                        $data[
                            'doctor_id'
                        ],
                        $data[
                            'appointment_date'
                        ],
                        $slot[
                            'start_time'
                        ],
                        $slot[
                            'end_time'
                        ],
                        null,
                        $slot[
                            'schedule'
                        ]
                    );


                $appointment =
                    Appointment::create([
                        ...$data,

                        'doctor_schedule_id' =>
                            $slot[
                                'schedule'
                            ]->id,

                        'start_time' =>
                            $slot[
                                'start_time'
                            ],

                        'end_time' =>
                            $slot[
                                'end_time'
                            ],

                        'appointment_code' =>
                            null,

                        'status' =>
                            'SCHEDULED',

                        'created_by' =>
                            $createdBy,
                    ]);


                $appointment
                    ->appointment_code =
                    sprintf(
                        'APT-%s-%06d',
                        now()->format('Y'),
                        $appointment->id
                    );


                $appointment->save();


                return $this
                    ->loadAppointment(
                        $appointment
                    );
            }
        );
    }


    public function update(
        Appointment $appointment,
        array $data
    ): Appointment {

        if (
            $appointment->status
            !== 'SCHEDULED'
        ) {
            throw ValidationException::withMessages([
                'appointment' =>
                    'Only scheduled appointments can be rescheduled or edited.',
            ]);
        }


        return DB::transaction(
            function () use (
                $appointment,
                $data
            ) {

                $slot =
                    $this
                        ->resolveSlot(
                            $data
                        );


                $this
                    ->ensureSlotAvailable(
                        $data[
                            'patient_id'
                        ],
                        $data[
                            'doctor_id'
                        ],
                        $data[
                            'appointment_date'
                        ],
                        $slot[
                            'start_time'
                        ],
                        $slot[
                            'end_time'
                        ],
                        $appointment->id,
                        $slot[
                            'schedule'
                        ]
                    );


                $appointment->update([
                    ...$data,

                    'doctor_schedule_id' =>
                        $slot[
                            'schedule'
                        ]->id,

                    'start_time' =>
                        $slot[
                            'start_time'
                        ],

                    'end_time' =>
                        $slot[
                            'end_time'
                        ],
                ]);


                return $this
                    ->loadAppointment(
                        $appointment
                    );
            }
        );
    }


    public function updateStatus(
        Appointment $appointment,
        string $newStatus,
        User $user,
        ?string $cancellationReason = null
    ): Appointment {

        $this->ensureCanView(
            $appointment,
            $user
        );


        $role =
            $user->role?->slug;


        $allowedByRole = [
            'ADMIN' => [
                'CHECKED_IN',
                'IN_PROGRESS',
                'COMPLETED',
                'CANCELLED',
                'NO_SHOW',
            ],

            'RECEPTIONIST' => [
                'CHECKED_IN',
                'CANCELLED',
                'NO_SHOW',
            ],

            'NURSE' => [
                'CHECKED_IN',
            ],

            'DOCTOR' => [
                'IN_PROGRESS',
                'COMPLETED',
                'CANCELLED',
            ],
        ];


        if (
            ! in_array(
                $newStatus,
                $allowedByRole[$role] ?? [],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'Your role cannot perform this appointment status change.',
            ]);
        }


        $transitions = [
            'SCHEDULED' => [
                'CHECKED_IN',
                'CANCELLED',
                'NO_SHOW',
            ],

            'CHECKED_IN' => [
                'IN_PROGRESS',
                'CANCELLED',
            ],

            'IN_PROGRESS' => [
                'COMPLETED',
                'CANCELLED',
            ],

            'COMPLETED' => [],

            'CANCELLED' => [],

            'NO_SHOW' => [],
        ];


        if (
            ! in_array(
                $newStatus,
                $transitions[
                    $appointment->status
                ] ?? [],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    "Cannot change appointment from {$appointment->status} to {$newStatus}.",
            ]);
        }


        if (
            $newStatus === 'CANCELLED'
            && ! $cancellationReason
        ) {
            throw ValidationException::withMessages([
                'cancellation_reason' =>
                    'A cancellation reason is required.',
            ]);
        }


        $appointment->status =
            $newStatus;


        if ($newStatus === 'CHECKED_IN') {
            $appointment->checked_in_at =
                now();
        }


        if ($newStatus === 'IN_PROGRESS') {
            $appointment->started_at =
                now();
        }


        if ($newStatus === 'COMPLETED') {
            $appointment->completed_at =
                now();
        }


        if ($newStatus === 'CANCELLED') {

            $appointment->cancelled_at =
                now();

            $appointment->cancelled_by =
                $user->id;

            $appointment
                ->cancellation_reason =
                $cancellationReason;
        }


        $appointment->save();


        return $this
            ->loadAppointment(
                $appointment
            );
    }


    public function ensureCanView(
        Appointment $appointment,
        User $user
    ): void {

        if (
            $user->role?->slug
            !== 'DOCTOR'
        ) {
            return;
        }


        if (
            ! $user->doctor
            || $user->doctor->id
                !== $appointment->doctor_id
        ) {
            abort(403);
        }
    }


    private function resolveSlot(
        array $data
    ): array {

        $patient =
            Patient::query()
                ->whereKey(
                    $data['patient_id']
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        if (! $patient) {
            throw ValidationException::withMessages([
                'patient_id' =>
                    'The selected patient is not active.',
            ]);
        }


        $this
            ->validateDoctorDepartment(
                $data['doctor_id'],
                $data['department_id']
            );


        $date =
            CarbonImmutable::parse(
                $data[
                    'appointment_date'
                ]
            );


        $requestedStart =
            CarbonImmutable::parse(
                $data[
                    'appointment_date'
                ]
                . ' '
                . $data[
                    'start_time'
                ]
            );


        if (
            $requestedStart
                ->lessThanOrEqualTo(now())
        ) {
            throw ValidationException::withMessages([
                'start_time' =>
                    'Appointment time must be in the future.',
            ]);
        }


        $day =
            strtoupper(
                $date->format('l')
            );


        $schedules =
            DoctorSchedule::query()
                ->where(
                    'doctor_id',
                    $data['doctor_id']
                )
                ->where(
                    'department_id',
                    $data['department_id']
                )
                ->where(
                    'day_of_week',
                    $day
                )
                ->where(
                    'is_active',
                    true
                )
                ->get();


        foreach ($schedules as $schedule) {

            $scheduleStart =
                CarbonImmutable::parse(
                    $data[
                        'appointment_date'
                    ]
                    . ' '
                    . $schedule
                        ->start_time
                );


            $scheduleEnd =
                CarbonImmutable::parse(
                    $data[
                        'appointment_date'
                    ]
                    . ' '
                    . $schedule
                        ->end_time
                );


            $duration =
                (int)
                $schedule
                    ->slot_duration_minutes;


            $requestedEnd =
                $requestedStart
                    ->addMinutes(
                        $duration
                    );


            if (
                $requestedStart
                    ->lessThan(
                        $scheduleStart
                    )
                ||
                $requestedEnd
                    ->greaterThan(
                        $scheduleEnd
                    )
            ) {
                continue;
            }


            $minutesFromStart =
                $scheduleStart
                    ->diffInMinutes(
                        $requestedStart
                    );


            if (
                $minutesFromStart
                % $duration
                !== 0
            ) {
                continue;
            }


            return [
                'schedule' =>
                    $schedule,

                'start_time' =>
                    $requestedStart
                        ->format('H:i'),

                'end_time' =>
                    $requestedEnd
                        ->format('H:i'),
            ];
        }


        throw ValidationException::withMessages([
            'start_time' =>
                'The selected time is outside the doctor schedule.',
        ]);
    }


    private function ensureSlotAvailable(
        int $patientId,
        int $doctorId,
        string $date,
        string $start,
        string $end,
        ?int $ignoreAppointmentId,
        DoctorSchedule $schedule
    ): void {

        $doctorConflict =
            Appointment::query()
                ->where(
                    'doctor_id',
                    $doctorId
                )
                ->whereDate(
                    'appointment_date',
                    $date
                )
                ->whereIn(
                    'status',
                    self::BLOCKING_STATUSES
                )
                ->when(
                    $ignoreAppointmentId,
                    fn ($query) =>
                        $query->whereKeyNot(
                            $ignoreAppointmentId
                        )
                )
                ->where(
                    'start_time',
                    '<',
                    $end
                )
                ->where(
                    'end_time',
                    '>',
                    $start
                )
                ->lockForUpdate()
                ->exists();


        if ($doctorConflict) {
            throw ValidationException::withMessages([
                'start_time' =>
                    'This doctor already has an appointment during the selected time.',
            ]);
        }


        $patientConflict =
            Appointment::query()
                ->where(
                    'patient_id',
                    $patientId
                )
                ->whereDate(
                    'appointment_date',
                    $date
                )
                ->whereIn(
                    'status',
                    self::BLOCKING_STATUSES
                )
                ->when(
                    $ignoreAppointmentId,
                    fn ($query) =>
                        $query->whereKeyNot(
                            $ignoreAppointmentId
                        )
                )
                ->where(
                    'start_time',
                    '<',
                    $end
                )
                ->where(
                    'end_time',
                    '>',
                    $start
                )
                ->lockForUpdate()
                ->exists();


        if ($patientConflict) {
            throw ValidationException::withMessages([
                'patient_id' =>
                    'This patient already has another appointment during the selected time.',
            ]);
        }


        if ($schedule->max_appointments) {

            $count =
                Appointment::query()
                    ->where(
                        'doctor_schedule_id',
                        $schedule->id
                    )
                    ->whereDate(
                        'appointment_date',
                        $date
                    )
                    ->whereIn(
                        'status',
                        self::BLOCKING_STATUSES
                    )
                    ->when(
                        $ignoreAppointmentId,
                        fn ($query) =>
                            $query->whereKeyNot(
                                $ignoreAppointmentId
                            )
                    )
                    ->lockForUpdate()
                    ->count();


            if (
                $count >=
                $schedule->max_appointments
            ) {
                throw ValidationException::withMessages([
                    'start_time' =>
                        'The maximum number of appointments for this doctor schedule has been reached.',
                ]);
            }
        }
    }


    private function validateDoctorDepartment(
        int $doctorId,
        int $departmentId
    ): void {

        $department =
            Department::query()
                ->whereKey(
                    $departmentId
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        if (! $department) {
            throw ValidationException::withMessages([
                'department_id' =>
                    'The selected department is not active.',
            ]);
        }


        $doctor =
            Doctor::query()
                ->with('user:id,is_active')
                ->whereKey(
                    $doctorId
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        if (
            ! $doctor
            || ! $doctor->user
            || ! $doctor->user->is_active
        ) {
            throw ValidationException::withMessages([
                'doctor_id' =>
                    'The selected doctor is not active.',
            ]);
        }


        $assigned =
            $doctor
                ->departments()
                ->where(
                    'departments.id',
                    $departmentId
                )
                ->where(
                    'departments.is_active',
                    true
                )
                ->exists();


        if (! $assigned) {
            throw ValidationException::withMessages([
                'department_id' =>
                    'The selected doctor is not assigned to this department.',
            ]);
        }
    }


    private function loadAppointment(
        Appointment $appointment
    ): Appointment {

        return $appointment
            ->fresh()
            ->load([
                'patient:id,patient_code,first_name,last_name,phone',

                'doctor:id,user_id,doctor_code,specialization',

                'doctor.user:id,name',

                'department:id,code,name',

                'createdBy:id,name',

                'cancelledBy:id,name',
            ]);
    }
}