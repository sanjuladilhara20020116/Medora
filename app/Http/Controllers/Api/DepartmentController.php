<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Departments\StoreDepartmentRequest;
use App\Http\Requests\Departments\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentService $departmentService
    ) {
    }


    public function index(Request $request)
    {
        $filters =
            $request->validate([
                'search' =>
                    'nullable|string|max:255',

                'status' =>
                    'nullable|in:active,inactive',

                'per_page' =>
                    'nullable|integer|min:5|max:100',
            ]);


        return DepartmentResource::collection(
            $this
                ->departmentService
                ->paginate($filters)
        )->additional([
            'success' => true,

            'message' =>
                'Departments retrieved successfully.',
        ]);
    }


    public function store(
        StoreDepartmentRequest $request
    ): JsonResponse {

        $department =
            $this
                ->departmentService
                ->create(
                    $request->validated()
                );


        return response()->json([
            'success' => true,

            'message' =>
                'Department created successfully.',

            'data' =>
                (new DepartmentResource(
                    $department
                ))->resolve(),
        ], 201);
    }


    public function show(
        Department $department
    ): JsonResponse {

        $department->loadCount(
            'doctors'
        );


        return response()->json([
            'success' => true,

            'data' =>
                (new DepartmentResource(
                    $department
                ))->resolve(),
        ]);
    }


    public function update(
        UpdateDepartmentRequest $request,
        Department $department
    ): JsonResponse {

        $department =
            $this
                ->departmentService
                ->update(
                    $department,
                    $request->validated()
                );


        return response()->json([
            'success' => true,

            'message' =>
                'Department updated successfully.',

            'data' =>
                (new DepartmentResource(
                    $department
                ))->resolve(),
        ]);
    }


    public function destroy(
        Department $department
    ): JsonResponse {

        $this
            ->departmentService
            ->archive($department);


        return response()->json([
            'success' => true,

            'message' =>
                'Department archived successfully.',
        ]);
    }
}