<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Requests\Appointments\UpdateAppointmentRequest;
use App\Http\Requests\Appointments\UpdateAppointmentStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentService $appointmentService
    ) {
    }


    public function index(Request $request)
    {
        $filters =
            $request->validate([
                'search' =>
                    'nullable|string|max:255',

                'status' =>
                    'nullable|string|max:30',

                'appointment_date' =>
                    'nullable|date',

                'doctor_id' =>
                    'nullable|integer|exists:doctors,id',

                'department_id' =>
                    'nullable|integer|exists:departments,id',

                'patient_id' =>
                    'nullable|integer|exists:patients,id',

                'per_page' =>
                    'nullable|integer|min:5|max:50',
            ]);


        $user =
            Auth::guard('api')->user();


        return AppointmentResource::collection(
            $this
                ->appointmentService
                ->paginate(
                    $filters,
                    $user
                )
        )->additional([
            'success' => true,

            'message' =>
                'Appointments retrieved successfully.',
        ]);
    }


    public function availability(
        Request $request
    ): JsonResponse {

        $data =
            $request->validate([
                'doctor_id' =>
                    'required|integer|exists:doctors,id',

                'department_id' =>
                    'required|integer|exists:departments,id',

                'date' =>
                    'required|date|after_or_equal:today',
            ]);


        return response()->json([
            'success' => true,

            'message' =>
                'Appointment availability retrieved successfully.',

            'data' =>
                $this
                    ->appointmentService
                    ->availability(
                        (int)
                        $data['doctor_id'],

                        (int)
                        $data['department_id'],

                        $data['date']
                    ),
        ]);
    }


    public function store(
        StoreAppointmentRequest $request
    ): JsonResponse {

        $appointment =
            $this
                ->appointmentService
                ->create(
                    $request->validated(),
                    Auth::guard('api')->id()
                );


        return response()->json([
            'success' => true,

            'message' =>
                'Appointment booked successfully.',

            'data' =>
                (new AppointmentResource(
                    $appointment
                ))->resolve(),
        ], 201);
    }


    public function show(
        Appointment $appointment
    ): JsonResponse {

        $user =
            Auth::guard('api')->user();


        $this
            ->appointmentService
            ->ensureCanView(
                $appointment,
                $user
            );


        $appointment->load([
            'patient:id,patient_code,first_name,last_name,phone',

            'doctor:id,user_id,doctor_code,specialization',

            'doctor.user:id,name',

            'department:id,code,name',

            'createdBy:id,name',

            'cancelledBy:id,name',
        ]);


        return response()->json([
            'success' => true,

            'message' =>
                'Appointment retrieved successfully.',

            'data' =>
                (new AppointmentResource(
                    $appointment
                ))->resolve(),
        ]);
    }


    public function update(
        UpdateAppointmentRequest $request,
        Appointment $appointment
    ): JsonResponse {

        $appointment =
            $this
                ->appointmentService
                ->update(
                    $appointment,
                    $request->validated()
                );


        return response()->json([
            'success' => true,

            'message' =>
                'Appointment updated successfully.',

            'data' =>
                (new AppointmentResource(
                    $appointment
                ))->resolve(),
        ]);
    }


    public function updateStatus(
        UpdateAppointmentStatusRequest $request,
        Appointment $appointment
    ): JsonResponse {

        $data =
            $request->validated();


        $appointment =
            $this
                ->appointmentService
                ->updateStatus(
                    $appointment,

                    $data['status'],

                    Auth::guard('api')->user(),

                    $data[
                        'cancellation_reason'
                    ] ?? null
                );


        return response()->json([
            'success' => true,

            'message' =>
                'Appointment status updated successfully.',

            'data' =>
                (new AppointmentResource(
                    $appointment
                ))->resolve(),
        ]);
    }
}