@extends('layouts.app')

@section('title', 'Laboratory Test Catalogue | Medora HMS')
@section('page', 'app')
@section('header', 'Laboratory Test Catalogue')

@section('content')

<div id="labTestsPage" class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Laboratory Test Catalogue</h2>
            <p class="mt-1 text-sm text-slate-500">Configure active tests, specimens, reference ranges and turnaround times.</p>
        </div>
        <a href="{{ route('laboratory.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold">Back to Requests</a>
    </div>

    <div id="labTestsError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <section id="labTestFormSection" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4"><h3 id="labTestFormHeading" class="font-bold">Add Laboratory Test</h3><button id="cancelLabTestEditButton" type="button" class="hidden text-sm font-semibold text-slate-500">Cancel edit</button></div>
        <form id="labTestForm" class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <input name="name" required placeholder="Test name" class="rounded-xl border border-slate-300 px-4 py-3">
            <input name="category" placeholder="Category (e.g. Hematology)" class="rounded-xl border border-slate-300 px-4 py-3">
            <input name="specimen_type" required placeholder="Specimen type (e.g. Blood)" class="rounded-xl border border-slate-300 px-4 py-3">
            <input name="unit" placeholder="Unit (optional)" class="rounded-xl border border-slate-300 px-4 py-3">
            <input name="reference_range" placeholder="Reference range" class="rounded-xl border border-slate-300 px-4 py-3">
            <input name="turnaround_hours" type="number" min="1" placeholder="Turnaround hours" class="rounded-xl border border-slate-300 px-4 py-3">
            <input name="price" type="number" min="0" step="0.01" value="0" required placeholder="Price" class="rounded-xl border border-slate-300 px-4 py-3">
            <select name="is_active" class="rounded-xl border border-slate-300 px-4 py-3"><option value="1">Active</option><option value="0">Inactive</option></select>
            <textarea name="notes" rows="2" placeholder="Notes (optional)" class="rounded-xl border border-slate-300 px-4 py-3 lg:col-span-3"></textarea>
            <div class="flex justify-end lg:col-span-3"><button id="labTestSaveButton" type="submit" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white">Save Test</button></div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form id="labTestsFilters" class="grid gap-3 border-b border-slate-200 p-5 md:grid-cols-3">
            <input id="labTestsSearch" type="search" placeholder="Test code, name or category..." class="rounded-xl border border-slate-300 px-4 py-3">
            <select id="labTestsStatusFilter" class="rounded-xl border border-slate-300 px-4 py-3"><option value="active">Active tests</option><option value="inactive">Inactive tests</option><option value="">All tests</option></select>
            <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-3 font-semibold text-white">Search</button>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full"><thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500"><th class="px-5 py-4">Test</th><th class="px-5 py-4">Specimen</th><th class="px-5 py-4">Reference</th><th class="px-5 py-4">Turnaround</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Action</th></tr></thead><tbody id="labTestsTableBody" class="divide-y divide-slate-100"></tbody></table>
        </div>
    </section>
</div>

@endsection
