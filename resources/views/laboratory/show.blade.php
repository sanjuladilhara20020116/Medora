@extends('layouts.app')

@section('title', 'Laboratory Request | Medora HMS')
@section('page', 'app')
@section('header', 'Laboratory Request')

@section('content')

<div id="labRequestShowPage" data-lab-request-id="{{ $labRequestId }}" class="mx-auto max-w-6xl">
    <div id="labRequestShowError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p id="labRequestCode" class="text-sm font-semibold text-cyan-700">Loading...</p>
                <h2 id="labRequestTestName" class="mt-2 text-3xl font-bold text-slate-950">Loading...</h2>
                <p id="labRequestStatus" class="mt-2 text-sm font-semibold text-slate-500"></p>
            </div>
            <button id="printLabReportButton" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Print Report</button>
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['labRequestPatient', 'Patient'],
                ['labRequestDoctor', 'Doctor'],
                ['labRequestPriority', 'Priority'],
                ['labRequestRequestedAt', 'Requested'],
                ['labRequestSpecimen', 'Specimen'],
                ['labRequestMedicalRecord', 'Medical Record'],
                ['labRequestSampleStatus', 'Sample Status'],
                ['labRequestCollectedAt', 'Collected'],
            ] as [$id, $label])
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <p id="{{ $id }}" class="mt-1 text-sm font-semibold text-slate-900">—</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="font-bold">Clinical Notes</h3>
        <p id="labRequestClinicalNotes" class="mt-4 whitespace-pre-wrap text-sm text-slate-700">—</p>
    </section>

    <section id="sampleCollectionSection" class="mt-6 hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="font-bold">Sample Collection</h3>
        <p class="mt-1 text-sm text-slate-500">Record the specimen identifier and its condition before processing.</p>
        <form id="sampleCollectionForm" class="mt-5 grid gap-4 md:grid-cols-2">
            <input name="sample_identifier" required placeholder="Sample identifier / barcode" class="rounded-xl border border-slate-300 px-4 py-3">
            <select name="specimen_condition" required class="rounded-xl border border-slate-300 px-4 py-3">
                <option value="ACCEPTABLE">Acceptable</option>
                <option value="HEMOLYZED">Hemolyzed</option>
                <option value="CLOTTED">Clotted</option>
                <option value="INSUFFICIENT">Insufficient</option>
                <option value="OTHER">Other</option>
            </select>
            <textarea name="sample_notes" rows="3" placeholder="Sample notes (optional)" class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"></textarea>
            <div class="flex justify-end md:col-span-2"><button type="submit" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white">Record Sample Collection</button></div>
        </form>
    </section>

    <section id="processingSection" class="mt-6 hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="font-bold">Laboratory Processing</h3>
        <p class="mt-1 text-sm text-slate-500">The sample is ready to be processed.</p>
        <button id="startProcessingButton" type="button" class="mt-5 rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white">Start Processing</button>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="font-bold">Laboratory Result</h3>
                <p class="mt-1 text-sm text-slate-500">Result entry completes this test request and enables the report.</p>
            </div>
            <button id="showLabResultFormButton" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Enter / Edit Result</button>
        </div>

        <div id="labResultSummary" class="mt-5 text-sm text-slate-500">No result has been entered.</div>

        <form id="labResultForm" class="mt-6 hidden border-t border-slate-200 pt-6">
            <div class="grid gap-5 md:grid-cols-2">
                <textarea name="result_value" rows="4" required placeholder="Result value" class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"></textarea>
                <input id="labResultUnit" name="unit" placeholder="Unit" class="rounded-xl border border-slate-300 px-4 py-3">
                <input id="labResultReferenceRange" name="reference_range" placeholder="Reference range" class="rounded-xl border border-slate-300 px-4 py-3">
                <select name="interpretation" required class="rounded-xl border border-slate-300 px-4 py-3">
                    <option value="NORMAL">Normal</option>
                    <option value="ABNORMAL">Abnormal</option>
                    <option value="CRITICAL">Critical</option>
                    <option value="INCONCLUSIVE">Inconclusive</option>
                </select>
                <input id="labResultedAt" name="resulted_at" type="datetime-local" required class="rounded-xl border border-slate-300 px-4 py-3">
                <textarea name="remarks" rows="4" placeholder="Remarks (optional)" class="rounded-xl border border-slate-300 px-4 py-3 md:col-span-2"></textarea>
            </div>
            <div class="mt-5 flex justify-end gap-3">
                <button id="cancelLabResultButton" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
                <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Save Result</button>
            </div>
        </form>
    </section>
</div>

@endsection
