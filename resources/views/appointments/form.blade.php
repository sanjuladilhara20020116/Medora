@extends('layouts.app')

@section('title', 'Appointment | Medora HMS')
@section('page', 'app')
@section('header', 'Appointment')

@section('content')

<div
    id="appointmentFormPage"
    data-appointment-id="{{ $appointmentId ?? '' }}"
    class="mx-auto max-w-5xl"
>

    <div class="mb-6">

        <h2 class="text-2xl font-bold text-slate-950">
            {{ isset($appointmentId)
                ? 'Edit Appointment'
                : 'Book Appointment' }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Select patient, department, doctor and an available time.
        </p>

    </div>


    <div
        id="appointmentFormError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <form
        id="appointmentForm"
        class="space-y-6"
    >

        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >

            <h3 class="font-bold">
                Patient & Clinical Service
            </h3>


            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <label class="text-sm font-medium">
                    Patient

                    <select
                        id="appointmentPatient"
                        name="patient_id"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                        <option value="">
                            Select patient
                        </option>
                    </select>
                </label>


                <label class="text-sm font-medium">
                    Department

                    <select
                        id="appointmentDepartment"
                        name="department_id"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                        <option value="">
                            Select department
                        </option>
                    </select>
                </label>


                <label class="text-sm font-medium">
                    Doctor

                    <select
                        id="appointmentDoctor"
                        name="doctor_id"
                        required
                        disabled
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                        <option value="">
                            Select department first
                        </option>
                    </select>
                </label>


                <label class="text-sm font-medium">
                    Appointment Date

                    <input
                        id="appointmentDate"
                        name="appointment_date"
                        type="date"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                </label>


                <label class="text-sm font-medium md:col-span-2">
                    Available Time

                    <select
                        id="appointmentTime"
                        name="start_time"
                        required
                        disabled
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                        <option value="">
                            Choose doctor and date first
                        </option>
                    </select>
                </label>

            </div>

        </section>


        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >

            <h3 class="font-bold">
                Appointment Details
            </h3>


            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <label class="text-sm font-medium">
                    Type

                    <select
                        name="appointment_type"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                        <option value="CONSULTATION">
                            Consultation
                        </option>

                        <option value="FOLLOW_UP">
                            Follow Up
                        </option>

                        <option value="PROCEDURE">
                            Procedure
                        </option>

                        <option value="OTHER">
                            Other
                        </option>
                    </select>
                </label>


                <label class="text-sm font-medium">
                    Priority

                    <select
                        name="priority"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                    >
                        <option value="NORMAL">
                            Normal
                        </option>

                        <option value="URGENT">
                            Urgent
                        </option>
                    </select>
                </label>


                <textarea
                    name="reason"
                    rows="3"
                    placeholder="Reason for appointment"
                    class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"
                ></textarea>


                <textarea
                    name="notes"
                    rows="3"
                    placeholder="Administrative notes"
                    class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"
                ></textarea>

            </div>

        </section>


        <div class="flex justify-end gap-3">

            <a
                href="{{ route('appointments.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold"
            >
                Cancel
            </a>


            <button
                id="appointmentSaveButton"
                type="submit"
                class="rounded-xl bg-slate-950 px-6 py-3 text-sm font-semibold text-white"
            >
                Save Appointment
            </button>

        </div>

    </form>

</div>

@endsection