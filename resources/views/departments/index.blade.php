@extends('layouts.app')

@section('title', 'Departments | Medora HMS')
@section('page', 'app')
@section('header', 'Departments')

@section('content')

<div
    id="departmentsPage"
    class="mx-auto max-w-7xl"
>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-950">
            Hospital Departments
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Manage hospital departments and doctor assignments.
        </p>
    </div>


    <div
        id="departmentError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <div class="grid gap-6 xl:grid-cols-[380px_1fr]">

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3
                id="departmentFormTitle"
                class="font-bold text-slate-950"
            >
                Add Department
            </h3>


            <form
                id="departmentForm"
                class="mt-5 space-y-4"
            >

                <input
                    id="departmentId"
                    type="hidden"
                >


                <input
                    name="code"
                    required
                    placeholder="Code e.g. CARD"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >


                <input
                    name="name"
                    required
                    placeholder="Department name"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >


                <input
                    name="phone"
                    placeholder="Phone"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >


                <input
                    name="email"
                    type="email"
                    placeholder="Email"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >


                <input
                    name="location"
                    placeholder="Location"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >


                <textarea
                    name="description"
                    rows="4"
                    placeholder="Description"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                ></textarea>


                <div class="flex gap-2">

                    <button
                        id="departmentSaveButton"
                        type="submit"
                        class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white"
                    >
                        Save Department
                    </button>


                    <button
                        id="departmentCancelButton"
                        type="button"
                        class="hidden rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold"
                    >
                        Cancel
                    </button>

                </div>

            </form>

        </section>


        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 p-5">

                <input
                    id="departmentSearch"
                    type="search"
                    placeholder="Search departments..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                >

            </div>


            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-slate-50">

                        <tr class="text-left text-xs uppercase tracking-wider text-slate-500">

                            <th class="px-5 py-4">
                                Department
                            </th>

                            <th class="px-5 py-4">
                                Location
                            </th>

                            <th class="px-5 py-4">
                                Doctors
                            </th>

                            <th class="px-5 py-4">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody
                        id="departmentsTableBody"
                        class="divide-y divide-slate-100"
                    ></tbody>

                </table>

            </div>

        </section>

    </div>

</div>

@endsection