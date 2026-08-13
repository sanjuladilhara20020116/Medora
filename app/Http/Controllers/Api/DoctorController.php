<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctors\StoreDoctorRequest;
use App\Http\Requests\Doctors\StoreDoctorScheduleRequest;
use App\Http\Requests\Doctors\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function __construct(
        private DoctorService $doctorService
    ) {
    }


    public function index(Request $request)
    {
        $filters =
            $request->validate([
                'search' =>
                    'nullable|string|max:255',

                'department_id' =>
                    'nullable|integer|exists:departments,id',

                'status' =>
                    'nullable|in:active,inactive',

                'per_page' =>
                    'nullable|integer|min:5|max:50',
            ]);


        return DoctorResource::collection(
            $this
                ->doctorService
                ->paginate($filters)
        )->additional([
            'success' => true,

            'message' =>
                'Doctors retrieved successfully.',
        ]);
    }


    public function store(
        StoreDoctorRequest $request
    ): JsonResponse {

        $doctor =
            $this
                ->doctorService
                ->create(
                    $request->validated()
                );


        return response()->json([
            'success' => true,

            'message' =>
                'Doctor created successfully.',

            'data' =>
                (new DoctorResource(
                    $doctor
                ))->resolve(),
        ], 201);
    }


    public function show(
        Doctor $doctor
    ): JsonResponse {

        $doctor->load([
            'user:id,name,username,email,phone',

            'departments:id,code,name',

            'schedules.department:id,name',
        ]);


        return response()->json([
            'success' => true,

            'data' =>
                (new DoctorResource(
                    $doctor
                ))->resolve(),
        ]);
    }


    public function update(
        UpdateDoctorRequest $request,
        Doctor $doctor
    ): JsonResponse {

        $doctor =
            $this
                ->doctorService
                ->update(
                    $doctor,
                    $request->validated()
                );


        return response()->json([
            'success' => true,

            'message' =>
                'Doctor updated successfully.',

            'data' =>
                (new DoctorResource(
                    $doctor
                ))->resolve(),
        ]);
    }


    public function destroy(
        Doctor $doctor
    ): JsonResponse {

        $this
            ->doctorService
            ->archive($doctor);


        return response()->json([
            'success' => true,

            'message' =>
                'Doctor archived successfully.',
        ]);
    }


    public function storeSchedule(
        StoreDoctorScheduleRequest $request,
        Doctor $doctor
    ): JsonResponse {

        $schedule =
            $this
                ->doctorService
                ->createSchedule(
                    $doctor,
                    $request->validated()
                );


        return response()->json([
            'success' => true,

            'message' =>
                'Doctor schedule added successfully.',

            'data' => [
                'id' =>
                    $schedule->id,
            ],
        ], 201);
    }


    public function destroySchedule(
        Doctor $doctor,
        DoctorSchedule $schedule
    ): JsonResponse {

        $this
            ->doctorService
            ->deleteSchedule(
                $doctor,
                $schedule
            );


        return response()->json([
            'success' => true,

            'message' =>
                'Doctor schedule removed successfully.',
        ]);
    }
}