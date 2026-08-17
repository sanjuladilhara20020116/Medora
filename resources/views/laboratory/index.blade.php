@extends('layouts.app')

@section('title', 'Laboratory | Medora HMS')
@section('page', 'app')
@section('header', 'Laboratory')

@section('content')

<div id="laboratoryIndexPage" class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Laboratory Management</h2>
            <p class="mt-1 text-sm text-slate-500">Track test requests, sample collection, results and printable laboratory reports.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a id="laboratoryTestsLink" href="{{ route('laboratory.tests') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold hover:bg-slate-50">Test Catalogue</a>
            <a id="createLabRequestLink" href="{{ route('laboratory.requests.create') }}" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">+ Request Test</a>
        </div>
    </div>

    <div id="laboratoryError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form id="laboratoryFilters" class="grid gap-3 border-b border-slate-200 p-5 lg:grid-cols-5">
            <input id="laboratorySearch" type="search" placeholder="Request, patient or test..." class="rounded-xl border border-slate-300 px-4 py-3">
            <select id="laboratoryStatusFilter" class="rounded-xl border border-slate-300 px-4 py-3">
                <option value="">All statuses</option>
                @foreach(['REQUESTED', 'SAMPLE_COLLECTED', 'PROCESSING', 'COMPLETED', 'CANCELLED'] as $status)
                    <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                @endforeach
            </select>
            <select id="laboratoryPatientFilter" class="rounded-xl border border-slate-300 px-4 py-3">
                <option value="">All patients</option>
            </select>
            <input id="laboratoryDateFilter" type="date" class="rounded-xl border border-slate-300 px-4 py-3">
            <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-3 font-semibold text-white hover:bg-cyan-700">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-4">Request</th>
                        <th class="px-5 py-4">Patient</th>
                        <th class="px-5 py-4">Test</th>
                        <th class="px-5 py-4">Doctor</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Action</th>
                    </tr>
                </thead>
                <tbody id="laboratoryTableBody" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
            <p id="laboratoryPaginationInfo" class="text-sm text-slate-500">Loading...</p>
            <div class="flex gap-2">
                <button id="laboratoryPrevPage" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm disabled:opacity-40">Previous</button>
                <button id="laboratoryNextPage" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm disabled:opacity-40">Next</button>
            </div>
        </div>
    </section>
</div>

@endsection
