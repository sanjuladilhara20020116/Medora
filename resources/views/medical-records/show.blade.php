@extends('layouts.app')

@section('title', 'Medical Record Details | Medora HMS')
@section('page', 'app')
@section('header', 'Medical Record Details')

@section('content')

<div id="medicalRecordShowPage" data-medical-record-id="{{ $medicalRecordId }}" class="mx-auto max-w-6xl">
    <div id="medicalRecordShowError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p id="medicalRecordCode" class="text-sm font-semibold text-cyan-700">Loading...</p>
                <h2 id="medicalRecordPatientName" class="mt-2 text-3xl font-bold text-slate-950">Loading...</h2>
                <p id="medicalRecordPatientSummary" class="mt-2 text-sm text-slate-500"></p>
            </div>
            <a id="medicalRecordEditLink" href="#" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Edit Record</a>
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['medicalRecordDoctor', 'Doctor'],
                ['medicalRecordRecordedAt', 'Recorded'],
                ['medicalRecordAppointment', 'Appointment'],
                ['medicalRecordFollowUp', 'Follow-up'],
            ] as [$id, $label])
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <p id="{{ $id }}" class="mt-1 text-sm font-semibold text-slate-900">—</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="font-bold">Clinical Record</h3>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            @foreach([
                ['medicalRecordComplaint', 'Chief Complaint'],
                ['medicalRecordDiagnosis', 'Diagnosis'],
                ['medicalRecordTreatmentPlan', 'Treatment Plan'],
                ['medicalRecordClinicalNotes', 'Clinical Notes'],
                ['medicalRecordAllergies', 'Known Allergies'],
                ['medicalRecordConditions', 'Chronic Conditions'],
            ] as [$id, $label])
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <p id="{{ $id }}" class="mt-1 whitespace-pre-wrap text-sm text-slate-700">—</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="font-bold">Treatment History</h3>
        <p class="mt-1 text-sm text-slate-500">Previous consultations and diagnoses for this patient.</p>
        <div id="treatmentHistory" class="mt-5 space-y-3"></div>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-bold">Prescription</h3>
                <p class="mt-1 text-sm text-slate-500">Medicines prescribed for this consultation.</p>
            </div>
            <button id="editPrescriptionButton" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Add / Edit Prescription</button>
        </div>
        <div id="prescriptionSummary" class="mt-5 text-sm text-slate-500">No prescription has been recorded.</div>

        <form id="prescriptionForm" class="mt-6 hidden border-t border-slate-200 pt-6">
            <div class="grid gap-5 md:grid-cols-2">
                <label class="text-sm font-medium">
                    Issued At
                    <input id="prescriptionIssuedAt" name="issued_at" type="datetime-local" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                </label>
                <textarea name="notes" rows="3" placeholder="Prescription notes" class="rounded-xl border border-slate-300 px-4 py-3"></textarea>
            </div>
            <div class="mt-5 flex items-center justify-between">
                <h4 class="font-semibold">Prescription Items</h4>
                <button id="addPrescriptionItem" type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold">+ Add Medicine</button>
            </div>
            <div id="prescriptionItems" class="mt-4 space-y-4"></div>
            <div class="mt-5 flex justify-end gap-3">
                <button id="cancelPrescriptionButton" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
                <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Save Prescription</button>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-bold">Medical Reports</h3>
                <p class="mt-1 text-sm text-slate-500">Reports are stored privately and available only to authorized clinical staff.</p>
            </div>
            <button id="showMedicalReportFormButton" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Upload Report</button>
        </div>

        <form id="medicalReportForm" class="mt-6 hidden border-t border-slate-200 pt-6">
            <div class="grid gap-4 md:grid-cols-2">
                <input name="title" required placeholder="Report title" class="rounded-xl border border-slate-300 px-4 py-3">
                <input name="report_type" required placeholder="Report type (e.g. Imaging)" class="rounded-xl border border-slate-300 px-4 py-3">
                <input name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" required class="rounded-xl border border-slate-300 px-4 py-3">
                <input name="notes" placeholder="Notes (optional)" class="rounded-xl border border-slate-300 px-4 py-3">
            </div>
            <div class="mt-4 flex justify-end gap-3">
                <button id="cancelMedicalReportButton" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
                <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Upload Report</button>
            </div>
        </form>

        <div id="medicalReportsList" class="mt-5 space-y-3"></div>
    </section>
</div>

@endsection
