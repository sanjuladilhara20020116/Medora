@extends('layouts.app')

@section('title', 'Reports & Analytics | Medora HMS')
@section('page', 'app')
@section('header', 'Reports & Analytics')

@section('content')
<div id="reportsPage" class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Reports &amp; Analytics</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">Review live patient, appointment, revenue, pharmacy, laboratory, and staff activity.</p>
        </div>
        <div class="flex flex-wrap gap-3 print:hidden">
            <button id="reportExportButton" type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Export CSV</button>
            <button id="reportPrintButton" type="button" class="rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Print report</button>
        </div>
    </div>

    <form id="reportFilters" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4 print:hidden">
        <label class="text-sm font-medium text-slate-700">From date<input id="reportFromDate" name="from_date" type="date" value="{{ now()->startOfMonth()->toDateString() }}" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"></label>
        <label class="text-sm font-medium text-slate-700">To date<input id="reportToDate" name="to_date" type="date" value="{{ now()->toDateString() }}" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"></label>
        <label class="text-sm font-medium text-slate-700">Detailed report<select id="reportType" name="report_type" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"><option value="patients">Patient report</option><option value="appointments">Appointment report</option><option value="revenue">Revenue report</option><option value="pharmacy">Pharmacy report</option><option value="laboratory">Laboratory report</option><option value="staff">Staff report</option></select></label>
        <div class="flex items-end"><button type="submit" class="w-full rounded-xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Apply filters</button></div>
    </form>

    <div id="reportsError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"></div>

    <section aria-label="Hospital overview" class="mb-6">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2"><h3 class="text-lg font-bold text-slate-950">Hospital overview</h3><p id="reportPeriod" class="text-sm text-slate-500">Loading live data…</p></div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">Registered Patients</p><p id="reportPatientTotal" class="mt-2 text-3xl font-bold text-slate-950">—</p><p id="reportNewPatients" class="mt-2 text-xs text-slate-500">— new in selected period</p></article>
            <article class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm"><p class="text-sm font-medium text-sky-800">Appointments</p><p id="reportAppointments" class="mt-2 text-3xl font-bold text-sky-950">—</p><p id="reportCompletedAppointments" class="mt-2 text-xs text-sky-800">— completed in selected period</p></article>
            <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm"><p class="text-sm font-medium text-emerald-800">Payments Received</p><p id="reportRevenue" class="mt-2 text-3xl font-bold text-emerald-950">—</p><p id="reportOutstanding" class="mt-2 text-xs text-emerald-800">— outstanding balance</p></article>
            <article class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm"><p class="text-sm font-medium text-violet-800">Laboratory Requests</p><p id="reportLabRequests" class="mt-2 text-3xl font-bold text-violet-950">—</p><p id="reportCompletedLabs" class="mt-2 text-xs text-violet-800">— completed in selected period</p></article>
            <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm"><p class="text-sm font-medium text-amber-800">Expired Medicine Batches</p><p id="reportExpiredBatches" class="mt-2 text-3xl font-bold text-amber-950">—</p><p id="reportDispensations" class="mt-2 text-xs text-amber-800">— dispensations in selected period</p></article>
            <article class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5 shadow-sm"><p class="text-sm font-medium text-cyan-800">Active Employees</p><p id="reportActiveEmployees" class="mt-2 text-3xl font-bold text-cyan-950">—</p><p id="reportPendingLeaves" class="mt-2 text-xs text-cyan-800">— pending leave requests</p></article>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5"><h3 id="reportDetailTitle" class="text-lg font-bold text-slate-950">Patient report</h3><p id="reportDetailDescription" class="mt-1 text-sm text-slate-500">Loading selected report…</p></div>
        <div class="grid gap-6 p-5 xl:grid-cols-5">
            <div class="xl:col-span-3"><h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Activity by date</h4><div id="reportChart" class="space-y-3" aria-live="polite"></div></div>
            <div class="xl:col-span-2"><h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Key breakdown</h4><div id="reportBreakdown" class="space-y-3"></div></div>
        </div>
        <div class="border-t border-slate-200 p-5"><h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Report details</h4><div id="reportDetails" class="overflow-x-auto"></div></div>
    </section>
</div>
@endsection
