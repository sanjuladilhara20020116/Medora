@extends('layouts.app')

@section('title', 'Appointment Details | Medora HMS')
@section('page', 'app')
@section('header', 'Appointment Details')

@section('content')

<div
    id="appointmentShowPage"
    data-appointment-id="{{ $appointmentId }}"
    class="mx-auto max-w-6xl"
>

    <div
        id="appointmentShowError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <section
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >

        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >

            <div>

                <p
                    id="appointmentCode"
                    class="text-sm font-semibold text-cyan-700"
                >
                    Loading...
                </p>

                <h2
                    id="appointmentPatientName"
                    class="mt-2 text-3xl font-bold text-slate-950"
                >
                    Loading...
                </h2>

                <p
                    id="appointmentStatus"
                    class="mt-2 text-sm font-semibold text-slate-500"
                ></p>

            </div>


            <a
                id="appointmentEditLink"
                href="#"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold"
            >
                Edit Appointment
            </a>

        </div>


        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

            @foreach([
                ['appointmentDoctorName', 'Doctor'],
                ['appointmentDepartmentName', 'Department'],
                ['appointmentDateValue', 'Date'],
                ['appointmentTimeValue', 'Time'],
                ['appointmentTypeValue', 'Type'],
                ['appointmentPriorityValue', 'Priority'],
                ['appointmentPatientPhone', 'Patient Phone'],
                ['appointmentCreatedBy', 'Booked By'],
            ] as [$id, $label])

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        {{ $label }}
                    </p>

                    <p
                        id="{{ $id }}"
                        class="mt-1 text-sm font-semibold text-slate-900"
                    >
                        —
                    </p>

                </div>

            @endforeach

        </div>

    </section>


    <section
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >

        <h3 class="font-bold">
            Reason & Notes
        </h3>


        <div class="mt-5 space-y-5">

            <div>
                <p class="text-xs text-slate-400">
                    Reason
                </p>

                <p
                    id="appointmentReason"
                    class="mt-1 text-sm"
                >
                    —
                </p>
            </div>


            <div>
                <p class="text-xs text-slate-400">
                    Notes
                </p>

                <p
                    id="appointmentNotes"
                    class="mt-1 text-sm"
                >
                    —
                </p>
            </div>

        </div>

    </section>


    <section
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >

        <h3 class="font-bold">
            Appointment Workflow
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            Available actions depend on your role and the current appointment status.
        </p>


        <div
            id="appointmentActions"
            class="mt-5 flex flex-wrap gap-3"
        ></div>

    </section>

</div>

@endsection