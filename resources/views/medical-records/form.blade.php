@extends('layouts.app')

@section('title', 'Medical Record | Medora HMS')
@section('page', 'app')
@section('header', 'Medical Record')

@section('content')

<div id="medicalRecordFormPage" data-medical-record-id="{{ $medicalRecordId ?? '' }}" class="mx-auto max-w-5xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-950">{{ isset($medicalRecordId) ? 'Edit Medical Record' : 'Create Medical Record' }}</h2>
        <p class="mt-1 text-sm text-slate-500">Record the consultation diagnosis, treatment plan and follow-up details.</p>
    </div>

    <div id="medicalRecordFormError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <form id="medicalRecordForm" class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-bold">Consultation</h3>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="text-sm font-medium">
                    Patient
                    <select id="medicalRecordPatient" name="patient_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                        <option value="">Select patient</option>
                    </select>
                </label>

                <label id="medicalRecordDoctorField" class="text-sm font-medium">
                    Doctor
                    <select id="medicalRecordDoctor" name="doctor_id" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                        <option value="">Select doctor</option>
                    </select>
                </label>

                <label class="text-sm font-medium">
                    Related Appointment <span class="font-normal text-slate-400">(optional)</span>
                    <select id="medicalRecordAppointment" name="appointment_id" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" disabled>
                        <option value="">Select a patient first</option>
                    </select>
                </label>

                <label class="text-sm font-medium">
                    Consultation Date & Time
                    <input id="medicalRecordRecordedAt" name="recorded_at" type="datetime-local" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-bold">Clinical Assessment</h3>
            <div class="mt-5 grid gap-5">
                <textarea name="chief_complaint" rows="3" placeholder="Chief complaint / presenting symptoms" class="rounded-xl border border-slate-300 px-4 py-3"></textarea>
                <textarea name="diagnosis" rows="4" required placeholder="Diagnosis" class="rounded-xl border border-slate-300 px-4 py-3"></textarea>
                <textarea name="treatment_plan" rows="4" placeholder="Treatment plan" class="rounded-xl border border-slate-300 px-4 py-3"></textarea>
                <textarea name="clinical_notes" rows="5" placeholder="Clinical notes" class="rounded-xl border border-slate-300 px-4 py-3"></textarea>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-bold">Follow-up</h3>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="text-sm font-medium">
                    Follow-up Date
                    <input name="follow_up_date" type="date" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                </label>
                <textarea name="follow_up_notes" rows="3" placeholder="Follow-up instructions" class="rounded-xl border border-slate-300 px-4 py-3"></textarea>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('medical-records.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold">Cancel</a>
            <button id="medicalRecordSaveButton" type="submit" class="rounded-xl bg-slate-950 px-6 py-3 text-sm font-semibold text-white">Save Medical Record</button>
        </div>
    </form>
</div>

@endsection
