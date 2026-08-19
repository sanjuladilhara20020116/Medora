@extends('layouts.app')

@section('title', 'Doctors | Medora HMS')
@section('page', 'app')
@section('header', 'Doctors')

@section('content')

<div
    id="doctorsIndexPage"
    class="app-workspace mx-auto max-w-7xl"
>

    <div class="workspace-heading flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-2xl font-bold text-slate-950">
                Doctor Management
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Manage hospital doctors, departments and schedules.
            </p>
        </div>


        <a
            id="createDoctorLink"
            href="{{ route('doctors.create') }}"
            class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white"
        >
            + Add Doctor
        </a>

    </div>


    <div
        id="doctorsError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <section class="workspace-panel workspace-panel--table">

        <div class="filter-bar grid gap-3 border-b border-slate-200 p-5 md:grid-cols-3">

            <input
                id="doctorSearch"
                type="search"
                placeholder="Search doctor..."
                class="rounded-xl border border-slate-300 px-4 py-3"
            >


            <select
                id="doctorDepartmentFilter"
                class="rounded-xl border border-slate-300 px-4 py-3"
            >
                <option value="">
                    All departments
                </option>
            </select>


            <select
                id="doctorStatusFilter"
                class="rounded-xl border border-slate-300 px-4 py-3"
            >
                <option value="">
                    All statuses
                </option>

                <option value="active">
                    Active
                </option>

                <option value="inactive">
                    Inactive
                </option>
            </select>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-slate-50">

                    <tr class="text-left text-xs uppercase tracking-wider text-slate-500">

                        <th class="px-5 py-4">
                            Doctor
                        </th>

                        <th class="px-5 py-4">
                            Specialization
                        </th>

                        <th class="px-5 py-4">
                            Department
                        </th>

                        <th class="px-5 py-4">
                            Room
                        </th>

                        <th class="px-5 py-4">
                            Status
                        </th>

                        <th class="px-5 py-4">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody
                    id="doctorsTableBody"
                    class="divide-y divide-slate-100"
                ></tbody>

            </table>

        </div>

    </section>

</div>

@endsection
