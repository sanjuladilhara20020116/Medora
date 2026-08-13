@extends('layouts.app')

@section('title', 'Patients | Medora HMS')
@section('page', 'app')
@section('header', 'Patients')

@section('content')

<div
    id="patientsIndexPage"
    class="mx-auto max-w-7xl"
>

    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >

        <div>
            <h2
                class="text-2xl font-bold text-slate-950"
            >
                Patient Management
            </h2>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                Register, search and manage hospital patients.
            </p>
        </div>

        <a
            href="{{ route('patients.create') }}"
            class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800"
        >
            + Register Patient
        </a>

    </div>


    <div
        id="patientsError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <section
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >

        <form
            id="patientFilters"
            class="grid gap-3 lg:grid-cols-4"
        >

            <input
                id="patientSearch"
                type="search"
                placeholder="Patient ID, name, phone, NIC..."
                class="rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-cyan-600"
            >

            <select
                id="patientStatusFilter"
                class="rounded-xl border border-slate-300 px-4 py-3 text-sm"
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

            <select
                id="patientGenderFilter"
                class="rounded-xl border border-slate-300 px-4 py-3 text-sm"
            >
                <option value="">
                    All genders
                </option>

                <option value="MALE">
                    Male
                </option>

                <option value="FEMALE">
                    Female
                </option>

                <option value="OTHER">
                    Other
                </option>
            </select>

            <button
                type="submit"
                class="rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700"
            >
                Search
            </button>

        </form>

    </section>


    <section
        class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >

        <div class="overflow-x-auto">

            <table
                class="min-w-full divide-y divide-slate-200"
            >

                <thead class="bg-slate-50">

                    <tr
                        class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                    >
                        <th class="px-5 py-4">
                            Patient
                        </th>

                        <th class="px-5 py-4">
                            Contact
                        </th>

                        <th class="px-5 py-4">
                            DOB
                        </th>

                        <th class="px-5 py-4">
                            Gender
                        </th>

                        <th class="px-5 py-4">
                            Blood
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
                    id="patientsTableBody"
                    class="divide-y divide-slate-100"
                ></tbody>

            </table>

        </div>


        <div
            class="flex items-center justify-between border-t border-slate-200 px-5 py-4"
        >

            <p
                id="patientsPaginationInfo"
                class="text-sm text-slate-500"
            >
                Loading...
            </p>

            <div class="flex gap-2">

                <button
                    id="patientsPrevPage"
                    type="button"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm disabled:opacity-40"
                >
                    Previous
                </button>

                <button
                    id="patientsNextPage"
                    type="button"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm disabled:opacity-40"
                >
                    Next
                </button>

            </div>

        </div>

    </section>

</div>

@endsection