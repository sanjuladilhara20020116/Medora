@extends('layouts.app')

@section('title', 'Appointments | Medora HMS')
@section('page', 'app')
@section('header', 'Appointments')

@section('content')

<div
    id="appointmentsIndexPage"
    class="mx-auto max-w-7xl"
>

    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >

        <div>
            <h2 class="text-2xl font-bold text-slate-950">
                Appointment Management
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Book and manage patient appointments.
            </p>
        </div>


        <a
            id="createAppointmentLink"
            href="{{ route('appointments.create') }}"
            class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white"
        >
            + Book Appointment
        </a>

    </div>


    <div
        id="appointmentsError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <section
        class="rounded-2xl border border-slate-200 bg-white shadow-sm"
    >

        <form
            id="appointmentFilters"
            class="grid gap-3 border-b border-slate-200 p-5 lg:grid-cols-5"
        >

            <input
                id="appointmentSearch"
                type="search"
                placeholder="Appointment, patient, doctor..."
                class="rounded-xl border border-slate-300 px-4 py-3"
            >


            <select
                id="appointmentStatusFilter"
                class="rounded-xl border border-slate-300 px-4 py-3"
            >
                <option value="">
                    All statuses
                </option>

                @foreach([
                    'SCHEDULED',
                    'CHECKED_IN',
                    'IN_PROGRESS',
                    'COMPLETED',
                    'CANCELLED',
                    'NO_SHOW'
                ] as $status)

                    <option value="{{ $status }}">
                        {{ str_replace('_', ' ', $status) }}
                    </option>

                @endforeach
            </select>


            <input
                id="appointmentDateFilter"
                type="date"
                class="rounded-xl border border-slate-300 px-4 py-3"
            >


            <select
                id="appointmentDepartmentFilter"
                class="rounded-xl border border-slate-300 px-4 py-3"
            >
                <option value="">
                    All departments
                </option>
            </select>


            <button
                type="submit"
                class="rounded-xl bg-cyan-600 px-4 py-3 font-semibold text-white"
            >
                Search
            </button>

        </form>


        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-slate-50">

                    <tr
                        class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                    >
                        <th class="px-5 py-4">
                            Appointment
                        </th>

                        <th class="px-5 py-4">
                            Patient
                        </th>

                        <th class="px-5 py-4">
                            Doctor
                        </th>

                        <th class="px-5 py-4">
                            Date / Time
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
                    id="appointmentsTableBody"
                    class="divide-y divide-slate-100"
                ></tbody>

            </table>

        </div>


        <div
            class="flex items-center justify-between border-t border-slate-200 px-5 py-4"
        >

            <p
                id="appointmentsPaginationInfo"
                class="text-sm text-slate-500"
            >
                Loading...
            </p>


            <div class="flex gap-2">

                <button
                    id="appointmentsPrevPage"
                    type="button"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm disabled:opacity-40"
                >
                    Previous
                </button>


                <button
                    id="appointmentsNextPage"
                    type="button"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm disabled:opacity-40"
                >
                    Next
                </button>

            </div>

        </div>

    </section>

</div>

@endsection