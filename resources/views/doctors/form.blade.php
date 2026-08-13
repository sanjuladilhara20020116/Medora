@extends('layouts.app')

@section('title', 'Doctor | Medora HMS')
@section('page', 'app')
@section('header', 'Doctor Management')

@section('content')

<div
    id="doctorFormPage"
    data-doctor-id="{{ $doctorId ?? '' }}"
    class="mx-auto max-w-5xl"
>

    <div
        id="doctorFormError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <form
        id="doctorForm"
        class="space-y-6"
    >

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="font-bold">
                Login Account
            </h3>


            <div class="mt-5 grid gap-4 md:grid-cols-2">

                <input
                    name="name"
                    required
                    placeholder="Doctor full name"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="username"
                    required
                    placeholder="Username"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="email"
                    required
                    type="email"
                    placeholder="Email"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="phone"
                    required
                    placeholder="Phone"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="password"
                    type="password"
                    placeholder="Password"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="password_confirmation"
                    type="password"
                    placeholder="Confirm password"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

            </div>

        </section>


        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="font-bold">
                Professional Information
            </h3>


            <div class="mt-5 grid gap-4 md:grid-cols-2">

                <input
                    name="registration_number"
                    required
                    placeholder="Medical registration number"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="designation"
                    placeholder="Designation"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="specialization"
                    placeholder="Specialization"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="experience_years"
                    type="number"
                    min="0"
                    value="0"
                    placeholder="Experience years"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="consultation_fee"
                    type="number"
                    min="0"
                    step="0.01"
                    value="0"
                    placeholder="Consultation fee"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="room_number"
                    placeholder="Room number"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="joined_at"
                    type="date"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >


                <textarea
                    name="qualifications"
                    rows="3"
                    placeholder="Qualifications"
                    class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"
                ></textarea>


                <textarea
                    name="biography"
                    rows="4"
                    placeholder="Biography"
                    class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"
                ></textarea>

            </div>

        </section>


        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h3 class="font-bold">
                Department Assignment
            </h3>


            <div
                id="doctorDepartmentCheckboxes"
                class="mt-5 grid gap-3 sm:grid-cols-2"
            >
                Loading departments...
            </div>


            <label class="mt-5 block text-sm font-medium">

                Primary Department

                <select
                    id="primaryDepartment"
                    class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                >
                    <option value="">
                        Select primary department
                    </option>
                </select>

            </label>

        </section>


        <div class="flex justify-end gap-3">

            <a
                href="{{ route('doctors.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold"
            >
                Cancel
            </a>


            <button
                id="doctorSaveButton"
                type="submit"
                class="rounded-xl bg-slate-950 px-6 py-3 text-sm font-semibold text-white"
            >
                Save Doctor
            </button>

        </div>

    </form>

</div>

@endsection