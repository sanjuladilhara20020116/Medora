@extends('layouts.app')

@section('title', 'Doctor Details | Medora HMS')
@section('page', 'app')
@section('header', 'Doctor Details')

@section('content')

<div
    id="doctorShowPage"
    data-doctor-id="{{ $doctorId }}"
    class="mx-auto max-w-6xl"
>

    <div
        id="doctorShowError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex items-start justify-between">

            <div>

                <p
                    id="doctorCode"
                    class="text-sm font-semibold text-cyan-700"
                >
                    Loading...
                </p>

                <h2
                    id="doctorName"
                    class="mt-2 text-3xl font-bold"
                >
                    Loading...
                </h2>

                <p
                    id="doctorSpecialization"
                    class="mt-2 text-sm text-slate-500"
                ></p>

            </div>


            <a
                id="doctorEditLink"
                href="#"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold"
            >
                Edit Doctor
            </a>

        </div>


        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

            <div>
                <p class="text-xs text-slate-400">
                    Registration
                </p>

                <p
                    id="doctorRegistration"
                    class="mt-1 font-semibold"
                >
                    —
                </p>
            </div>


            <div>
                <p class="text-xs text-slate-400">
                    Phone
                </p>

                <p
                    id="doctorPhone"
                    class="mt-1 font-semibold"
                >
                    —
                </p>
            </div>


            <div>
                <p class="text-xs text-slate-400">
                    Room
                </p>

                <p
                    id="doctorRoom"
                    class="mt-1 font-semibold"
                >
                    —
                </p>
            </div>


            <div>
                <p class="text-xs text-slate-400">
                    Departments
                </p>

                <p
                    id="doctorDepartments"
                    class="mt-1 font-semibold"
                >
                    —
                </p>
            </div>

        </div>

    </section>


    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <h3 class="font-bold">
            Weekly Schedule
        </h3>


        <form
            id="doctorScheduleForm"
            class="mt-5 grid gap-3 md:grid-cols-5"
        >

            <select
                id="scheduleDepartment"
                name="department_id"
                required
                class="rounded-xl border border-slate-300 px-3 py-3"
            ></select>


            <select
                name="day_of_week"
                required
                class="rounded-xl border border-slate-300 px-3 py-3"
            >
                @foreach([
                    'MONDAY',
                    'TUESDAY',
                    'WEDNESDAY',
                    'THURSDAY',
                    'FRIDAY',
                    'SATURDAY',
                    'SUNDAY'
                ] as $day)

                    <option value="{{ $day }}">
                        {{ ucfirst(strtolower($day)) }}
                    </option>

                @endforeach
            </select>


            <input
                name="start_time"
                type="time"
                required
                class="rounded-xl border border-slate-300 px-3 py-3"
            >


            <input
                name="end_time"
                type="time"
                required
                class="rounded-xl border border-slate-300 px-3 py-3"
            >


            <button
                type="submit"
                class="rounded-xl bg-cyan-600 px-4 py-3 font-semibold text-white"
            >
                Add Schedule
            </button>


            <input
                name="slot_duration_minutes"
                type="number"
                value="15"
                min="5"
                class="rounded-xl border border-slate-300 px-3 py-3"
            >

        </form>


        <div
            id="doctorSchedules"
            class="mt-6 divide-y divide-slate-100"
        ></div>

    </section>

</div>

@endsection