@extends('layouts.app')

@section('title', 'Medical Records | Medora HMS')
@section('page', 'app')
@section('header', 'Medical Records')

@section('content')

<div id="medicalRecordsIndexPage" class="app-workspace mx-auto max-w-7xl">
    <div class="workspace-heading flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Electronic Medical Records</h2>
            <p class="mt-1 text-sm text-slate-500">Clinical diagnoses, treatment history, prescriptions and medical reports.</p>
        </div>

        <a id="createMedicalRecordLink" href="{{ route('medical-records.create') }}" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
            + New Medical Record
        </a>
    </div>

    <div id="medicalRecordsError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <section class="workspace-panel workspace-panel--table">
        <form id="medicalRecordFilters" class="filter-bar grid gap-3 border-b border-slate-200 p-5 lg:grid-cols-5">
            <input id="medicalRecordSearch" type="search" placeholder="Record, patient or diagnosis..." class="rounded-xl border border-slate-300 px-4 py-3">
            <select id="medicalRecordPatientFilter" class="rounded-xl border border-slate-300 px-4 py-3">
                <option value="">All patients</option>
            </select>
            <select id="medicalRecordDoctorFilter" class="rounded-xl border border-slate-300 px-4 py-3">
                <option value="">All doctors</option>
            </select>
            <input id="medicalRecordDateFilter" type="date" class="rounded-xl border border-slate-300 px-4 py-3">
            <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-3 font-semibold text-white hover:bg-cyan-700">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-4">Record</th>
                        <th class="px-5 py-4">Patient</th>
                        <th class="px-5 py-4">Diagnosis</th>
                        <th class="px-5 py-4">Doctor</th>
                        <th class="px-5 py-4">Recorded</th>
                        <th class="px-5 py-4">Action</th>
                    </tr>
                </thead>
                <tbody id="medicalRecordsTableBody" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
            <p id="medicalRecordsPaginationInfo" class="text-sm text-slate-500">Loading...</p>
            <div class="flex gap-2">
                <button id="medicalRecordsPrevPage" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm disabled:opacity-40">Previous</button>
                <button id="medicalRecordsNextPage" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm disabled:opacity-40">Next</button>
            </div>
        </div>
    </section>
</div>

@endsection
