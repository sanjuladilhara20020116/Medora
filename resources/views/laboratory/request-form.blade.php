@extends('layouts.app')

@section('title', 'Request Laboratory Test | Medora HMS')
@section('page', 'app')
@section('header', 'Request Laboratory Test')

@section('content')

<div id="labRequestFormPage" class="mx-auto max-w-5xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-950">Request Laboratory Test</h2>
        <p class="mt-1 text-sm text-slate-500">Create one traceable request for a selected laboratory test.</p>
    </div>

    <div id="labRequestFormError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <form id="labRequestForm" class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-bold">Patient & Test</h3>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="text-sm font-medium">
                    Patient
                    <select id="labRequestPatient" name="patient_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                        <option value="">Select patient</option>
                    </select>
                </label>

                <label id="labRequestDoctorField" class="text-sm font-medium">
                    Doctor
                    <select id="labRequestDoctor" name="doctor_id" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                        <option value="">Select doctor</option>
                    </select>
                </label>

                <label class="text-sm font-medium">
                    Laboratory Test
                    <select id="labRequestTest" name="lab_test_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                        <option value="">Select laboratory test</option>
                    </select>
                </label>

                <label class="text-sm font-medium">
                    Related Medical Record <span class="font-normal text-slate-400">(optional)</span>
                    <select id="labRequestMedicalRecord" name="medical_record_id" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" disabled>
                        <option value="">Select a patient first</option>
                    </select>
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-bold">Clinical Request Details</h3>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="text-sm font-medium">
                    Priority
                    <select name="priority" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                        <option value="NORMAL">Normal</option>
                        <option value="URGENT">Urgent</option>
                    </select>
                </label>
                <div id="labTestHint" class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Select a test to view specimen requirements.</div>
                <textarea name="clinical_notes" rows="5" placeholder="Clinical notes or reason for the test" class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"></textarea>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('laboratory.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold">Cancel</a>
            <button id="labRequestSaveButton" type="submit" class="rounded-xl bg-slate-950 px-6 py-3 text-sm font-semibold text-white">Create Test Request</button>
        </div>
    </form>
</div>

@endsection
