<?php

namespace App\Services;

use App\Mail\DoctorAccountCreated;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DoctorService
{
    public function paginate(
        array $filters
    ): LengthAwarePaginator {

        $search =
            trim(
                (string)
                ($filters['search'] ?? '')
            );

        $perPage =
            min(
                max(
                    (int)
                    ($filters['per_page'] ?? 10),
                    5
                ),
                50
            );

        return Doctor::query()

            ->with([
                'user:id,name,username,email,phone',

                'departments:id,code,name',
            ])

            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        function ($query) use ($search) {

                            $query
                                ->where(
                                    'doctor_code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'registration_number',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'specialization',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhereHas(
                                    'user',
                                    function ($query) use ($search) {

                                        $query
                                            ->where(
                                                'name',
                                                'like',
                                                "%{$search}%"
                                            )

                                            ->orWhere(
                                                'email',
                                                'like',
                                                "%{$search}%"
                                            );
                                    }
                                );
                        }
                    );
                }
            )

            ->when(
                ! empty(
                    $filters['department_id']
                ),
                fn ($query) => $query->whereHas(
                    'departments',
                    fn ($query) => $query->where(
                        'departments.id',
                        $filters[
                            'department_id'
                        ]
                    )
                )
            )

            ->when(
                ($filters['status'] ?? null)
                    === 'active',
                fn ($query) => $query->where(
                    'is_active',
                    true
                )
            )

            ->when(
                ($filters['status'] ?? null)
                    === 'inactive',
                fn ($query) => $query->where(
                    'is_active',
                    false
                )
            )

            ->orderByDesc('id')

            ->paginate($perPage);
    }

    public function create(
        array $data
    ): Doctor {

        return DB::transaction(
            function () use ($data) {

                $doctorRole =
                    Role::query()
                        ->where(
                            'slug',
                            'DOCTOR'
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->firstOrFail();

                $this
                    ->validatePrimaryDepartment(
                        $data[
                            'department_ids'
                        ],
                        $data[
                            'primary_department_id'
                        ]
                    );

                $defaultPassword = Str::password(
                    14,
                    true,
                    true,
                    false,
                    false
                );

                $user =
                    User::create([
                        'name' => $data['name'],

                        'username' => $data['username'],

                        'email' => $data['email'],

                        'phone' => $data['phone'],

                        'password' => $defaultPassword,

                        'role_id' => $doctorRole->id,

                        'is_active' => true,
                    ]);

                $doctorData =
                    Arr::except(
                        $data,
                        [
                            'name',
                            'username',
                            'email',
                            'phone',
                            'department_ids',
                            'primary_department_id',
                        ]
                    );

                $doctor =
                    Doctor::create([
                        ...$doctorData,

                        'user_id' => $user->id,

                        'doctor_code' => null,

                        'is_active' => true,

                        'is_available' => true,
                    ]);

                $doctor->doctor_code =
                    sprintf(
                        'DOC-%s-%05d',
                        now()->format('Y'),
                        $doctor->id
                    );

                $doctor->save();

                $doctor
                    ->departments()
                    ->sync(
                        $this
                            ->departmentPayload(
                                $data[
                                    'department_ids'
                                ],

                                $data[
                                    'primary_department_id'
                                ]
                            )
                    );

                Mail::to($user->email)->send(
                    new DoctorAccountCreated(
                        $user,
                        $defaultPassword
                    )
                );

                return $this
                    ->loadDoctor($doctor);
            }
        );
    }

    public function update(
        Doctor $doctor,
        array $data
    ): Doctor {

        return DB::transaction(
            function () use (
                $doctor,
                $data
            ) {

                $this
                    ->validatePrimaryDepartment(
                        $data[
                            'department_ids'
                        ],

                        $data[
                            'primary_department_id'
                        ]
                    );

                $userData = [
                    'name' => $data['name'],

                    'username' => $data['username'],

                    'email' => $data['email'],

                    'phone' => $data['phone'],
                ];

                if (
                    ! empty(
                        $data['password']
                    )
                ) {
                    $userData['password'] =
                        $data['password'];
                }

                $doctor
                    ->user
                    ->update($userData);

                $doctorData =
                    Arr::except(
                        $data,
                        [
                            'name',
                            'username',
                            'email',
                            'phone',
                            'password',
                            'password_confirmation',
                            'department_ids',
                            'primary_department_id',
                        ]
                    );

                $doctor->update(
                    $doctorData
                );

                $doctor
                    ->departments()
                    ->sync(
                        $this
                            ->departmentPayload(
                                $data[
                                    'department_ids'
                                ],

                                $data[
                                    'primary_department_id'
                                ]
                            )
                    );

                return $this
                    ->loadDoctor($doctor);
            }
        );
    }

    public function archive(
        Doctor $doctor
    ): void {

        DB::transaction(
            function () use ($doctor) {

                $doctor->is_active =
                    false;

                $doctor->is_available =
                    false;

                $doctor->save();

                $doctor->user->is_active =
                    false;

                $doctor->user->save();

                $doctor->delete();

                $doctor->user->delete();
            }
        );
    }

    public function createSchedule(
        Doctor $doctor,
        array $data
    ): DoctorSchedule {

        $assigned =
            $doctor
                ->departments()
                ->where(
                    'departments.id',
                    $data['department_id']
                )
                ->exists();

        if (! $assigned) {
            throw ValidationException::withMessages([
                'department_id' => 'The doctor is not assigned to this department.',
            ]);
        }

        $overlap =
            $doctor
                ->schedules()
                ->where(
                    'day_of_week',
                    $data['day_of_week']
                )
                ->where(
                    'start_time',
                    '<',
                    $data['end_time']
                )
                ->where(
                    'end_time',
                    '>',
                    $data['start_time']
                )
                ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_time' => 'This schedule overlaps an existing doctor schedule.',
            ]);
        }

        return $doctor
            ->schedules()
            ->create([
                ...$data,

                'is_active' => true,
            ]);
    }

    public function deleteSchedule(
        Doctor $doctor,
        DoctorSchedule $schedule
    ): void {

        if (
            $schedule->doctor_id
            !== $doctor->id
        ) {
            abort(404);
        }

        $schedule->delete();
    }

    private function validatePrimaryDepartment(
        array $departmentIds,
        int $primaryDepartmentId
    ): void {

        if (
            ! in_array(
                $primaryDepartmentId,
                array_map(
                    'intval',
                    $departmentIds
                ),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'primary_department_id' => 'Primary department must be one of the selected departments.',
            ]);
        }
    }

    private function departmentPayload(
        array $departmentIds,
        int $primaryDepartmentId
    ): array {

        $payload = [];

        foreach (
            array_unique(
                array_map(
                    'intval',
                    $departmentIds
                )
            ) as $departmentId
        ) {

            $payload[$departmentId] = [
                'is_primary' => $departmentId
                    === $primaryDepartmentId,
            ];
        }

        return $payload;
    }

    private function loadDoctor(
        Doctor $doctor
    ): Doctor {

        return $doctor
            ->fresh()
            ->load([
                'user:id,name,username,email,phone',

                'departments:id,code,name',

                'schedules.department:id,name',
            ]);
    }
}
