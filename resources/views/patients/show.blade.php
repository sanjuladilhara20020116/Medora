@extends('layouts.app')

@section('title', 'Patient Details | Medora HMS')
@section('page', 'app')
@section('header', 'Patient Details')

@section('content')

<div
    id="patientShowPage"
    data-patient-id="{{ $patientId }}"
    class="mx-auto max-w-6xl"
>

    <div
        id="patientShowError"
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
                    id="patientCode"
                    class="text-sm font-semibold text-cyan-700"
                >
                    Loading...
                </p>

                <h2
                    id="patientName"
                    class="mt-2 text-3xl font-bold text-slate-950"
                >
                    Loading...
                </h2>

                <p
                    id="patientStatus"
                    class="mt-2 text-sm text-slate-500"
                ></p>

            </div>


            <div class="flex gap-2">

                <a
                    id="patientEditLink"
                    href="#"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold"
                >
                    Edit
                </a>

                <button
                    id="archivePatientButton"
                    type="button"
                    class="hidden rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    Archive
                </button>

            </div>

        </div>


        <div
            class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
        >

            @foreach([
                ['patientPhone', 'Phone'],
                ['patientEmail', 'Email'],
                ['patientDOB', 'Date of Birth'],
                ['patientGender', 'Gender'],
                ['patientBlood', 'Blood Group'],
                ['patientNIC', 'NIC / Passport'],
                ['patientCity', 'City'],
                ['patientEmergency', 'Emergency Contact'],
            ] as [$id, $label])

                <div>

                    <p
                        class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                    >
                        {{ $label }}
                    </p>

                    <p
                        id="{{ $id }}"
                        class="mt-1 text-sm font-medium text-slate-900"
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

        <h3 class="font-bold text-slate-950">
            Medical Information
        </h3>

        <div class="mt-5 space-y-4">

            <div>
                <p class="text-xs text-slate-400">
                    Allergies
                </p>

                <p
                    id="patientAllergies"
                    class="mt-1 text-sm"
                >
                    —
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">
                    Chronic Conditions
                </p>

                <p
                    id="patientConditions"
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
                    id="patientNotes"
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
            Patient Documents
        </h3>


        <form
            id="patientDocumentForm"
            class="mt-5 grid gap-3 md:grid-cols-4"
        >

            <input
                name="title"
                required
                placeholder="Document title"
                class="rounded-xl border border-slate-300 px-4 py-3"
            >

            <select
                name="document_type"
                required
                class="rounded-xl border border-slate-300 px-4 py-3"
            >
                <option value="">
                    Document type
                </option>

                <option value="IDENTIFICATION">
                    Identification
                </option>

                <option value="MEDICAL_REPORT">
                    Medical Report
                </option>

                <option value="LAB_REPORT">
                    Lab Report
                </option>

                <option value="OTHER">
                    Other
                </option>
            </select>

            <input
                name="file"
                type="file"
                required
                accept=".pdf,.jpg,.jpeg,.png"
                class="rounded-xl border border-slate-300 px-4 py-3"
            >

            <button
                type="submit"
                class="rounded-xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white"
            >
                Upload
            </button>

        </form>


        <div
            id="patientDocuments"
            class="mt-6 divide-y divide-slate-100"
        ></div>

    </section>

</div>

@endsection