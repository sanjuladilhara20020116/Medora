<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function paginate(
        array $filters
    ): LengthAwarePaginator {

        $perPage = min(
            max(
                (int) ($filters['per_page'] ?? 10),
                5
            ),
            100
        );

        $search = trim(
            (string) ($filters['search'] ?? '')
        );

        return Department::query()
            ->withCount('doctors')

            ->when(
                $search !== '',
                fn ($query) =>
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'location',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    )
            )

            ->when(
                ($filters['status'] ?? null)
                    === 'active',
                fn ($query) =>
                    $query->where(
                        'is_active',
                        true
                    )
            )

            ->when(
                ($filters['status'] ?? null)
                    === 'inactive',
                fn ($query) =>
                    $query->where(
                        'is_active',
                        false
                    )
            )

            ->orderBy('name')

            ->paginate($perPage);
    }


    public function create(
        array $data
    ): Department {

        $data['code'] =
            strtoupper(
                trim($data['code'])
            );

        $data['is_active'] =
            $data['is_active']
            ?? true;

        return Department::create($data);
    }


    public function update(
        Department $department,
        array $data
    ): Department {

        $data['code'] =
            strtoupper(
                trim($data['code'])
            );

        $department->update($data);

        return $department->fresh();
    }


    public function archive(
        Department $department
    ): void {

        $hasDoctors =
            $department
                ->doctors()
                ->where(
                    'doctors.is_active',
                    true
                )
                ->exists();

        if ($hasDoctors) {
            throw ValidationException::withMessages([
                'department' =>
                    'This department still has active doctors assigned to it.',
            ]);
        }

        $department->is_active = false;

        $department->save();

        $department->delete();
    }
}