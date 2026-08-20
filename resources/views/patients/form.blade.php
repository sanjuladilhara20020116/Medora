@extends('layouts.app')

@section(
    'title',
    isset($patientId)
        ? 'Edit Patient | Medora HMS'
        : 'Register Patient | Medora HMS'
)

@section('page', 'app')

@section(
    'header',
    isset($patientId)
        ? 'Edit Patient'
        : 'Register Patient'
)

@section('content')

<div
    id="patientFormPage"
    data-patient-id="{{ $patientId ?? '' }}"
    class="mx-auto max-w-5xl"
>

    <div class="mb-6">

        <h2
            id="patientFormTitle"
            class="text-2xl font-bold text-slate-950"
        >
            {{ isset($patientId)
                ? 'Edit Patient'
                : 'Register New Patient' }}
        </h2>

        <p
            class="mt-1 text-sm text-slate-500"
        >
            Enter the patient's hospital registration information. New patients receive patient portal sign-in details by email.
        </p>

    </div>


    <div
        id="patientFormError"
        class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    <form
        id="patientForm"
        class="space-y-6"
    >

        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >

            <h3 class="font-bold">
                Personal Information
            </h3>

            <div
                class="mt-5 grid gap-5 md:grid-cols-2"
            >

                <input
                    name="first_name"
                    required
                    placeholder="First name"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="last_name"
                    required
                    placeholder="Last name"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="date_of_birth"
                    required
                    type="date"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <select
                    name="gender"
                    required
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >
                    <option value="">
                        Select gender
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

                    <option value="PREFER_NOT_TO_SAY">
                        Prefer not to say
                    </option>
                </select>

                <select
                    name="blood_group"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >
                    <option value="">
                        Blood group
                    </option>

                    @foreach(
                        ['A+','A-','B+','B-','AB+','AB-','O+','O-']
                        as $blood
                    )
                        <option value="{{ $blood }}">
                            {{ $blood }}
                        </option>
                    @endforeach
                </select>

                <input
                    name="nic_passport"
                    placeholder="NIC / Passport"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

            </div>

        </section>


        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >

            <h3 class="font-bold">
                Contact Information
            </h3>

            <div
                class="mt-5 grid gap-5 md:grid-cols-2"
            >

                <input
                    name="email"
                    type="email"
                    @unless(isset($patientId)) required @endunless
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
                    name="alternate_phone"
                    placeholder="Alternate phone"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="city"
                    required
                    placeholder="City"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="address_line_1"
                    required
                    placeholder="Address line 1"
                    class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"
                >

                <input
                    name="address_line_2"
                    placeholder="Address line 2"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="district"
                    placeholder="District"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="postal_code"
                    placeholder="Postal code"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="country"
                    placeholder="Country"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

            </div>

        </section>


        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >

            <h3 class="font-bold">
                Emergency Contact
            </h3>

            <div
                class="mt-5 grid gap-5 md:grid-cols-3"
            >

                <input
                    name="emergency_contact_name"
                    required
                    placeholder="Contact name"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="emergency_contact_relation"
                    placeholder="Relationship"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

                <input
                    name="emergency_contact_phone"
                    required
                    placeholder="Phone"
                    class="rounded-xl border border-slate-300 px-4 py-3"
                >

            </div>

        </section>


        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >

            <h3 class="font-bold">
                Medical Notes
            </h3>

            <div class="mt-5 space-y-4">

                <textarea
                    name="allergies"
                    rows="3"
                    placeholder="Known allergies"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                ></textarea>

                <textarea
                    name="chronic_conditions"
                    rows="3"
                    placeholder="Chronic conditions"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                ></textarea>

                <textarea
                    name="notes"
                    rows="4"
                    placeholder="Additional notes"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                ></textarea>

            </div>

        </section>


        <div
            class="flex justify-end gap-3"
        >

            <a
                href="{{ route('patients.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold"
            >
                Cancel
            </a>

            <button
                id="patientSaveButton"
                type="submit"
                class="rounded-xl bg-slate-950 px-6 py-3 text-sm font-semibold text-white"
            >
                Save Patient
            </button>

        </div>

    </form>

</div>

@endsection
