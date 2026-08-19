@extends('layouts.app')

@section('title', 'Billing & Payments | Medora HMS')
@section('page', 'app')
@section('header', 'Billing & Payments')

@section('content')
<div id="billingIndexPage" class="app-workspace mx-auto max-w-7xl">
    <div class="workspace-heading flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950">Billing & Payments</h2>
            <p class="mt-1 text-sm text-slate-500">Generate invoices from hospital services and record patient payments.</p>
        </div>
        <a href="{{ route('billing.invoices.create') }}" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">+ Generate Invoice</a>
    </div>

    <div id="billingIndexError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="metric-card p-5"><p class="text-sm font-semibold text-slate-600">Invoices</p><p id="billingInvoiceCount" class="mt-3 text-3xl font-extrabold text-slate-950">—</p></article>
        <article class="metric-card metric-card--sky p-5"><p class="text-sm font-semibold text-slate-600">Total invoiced</p><p id="billingTotalInvoiced" class="mt-3 text-3xl font-extrabold text-slate-950">—</p></article>
        <article class="metric-card metric-card--emerald p-5"><p class="text-sm font-semibold text-emerald-800">Payments received</p><p id="billingTotalPaid" class="mt-3 text-3xl font-extrabold text-emerald-700">—</p></article>
        <article class="metric-card metric-card--amber p-5"><p class="text-sm font-semibold text-amber-800">Outstanding</p><p id="billingOutstanding" class="mt-3 text-3xl font-extrabold text-amber-950">—</p></article>
    </section>

    <section class="workspace-panel workspace-panel--table">
        <form id="billingInvoiceFilters" class="filter-bar grid gap-3 border-b border-slate-200 p-5 md:grid-cols-3">
            <input id="billingInvoiceSearch" type="search" placeholder="Invoice number or patient..." class="rounded-xl border border-slate-300 px-4 py-3">
            <select id="billingInvoiceStatus" class="rounded-xl border border-slate-300 px-4 py-3">
                <option value="">All statuses</option>
                <option value="UNPAID">Unpaid</option>
                <option value="PARTIALLY_PAID">Partially paid</option>
                <option value="PAID">Paid</option>
                <option value="CANCELLED">Cancelled</option>
            </select>
            <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-3 font-semibold text-white hover:bg-cyan-700">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500"><th class="px-5 py-4">Invoice</th><th class="px-5 py-4">Patient</th><th class="px-5 py-4">Issued</th><th class="px-5 py-4">Total</th><th class="px-5 py-4">Balance</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Action</th></tr></thead>
                <tbody id="billingInvoicesTable" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
            <p id="billingInvoicePagination" class="text-sm text-slate-500">Loading...</p>
            <div class="flex gap-2"><button id="billingInvoicePrev" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm disabled:opacity-40">Previous</button><button id="billingInvoiceNext" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm disabled:opacity-40">Next</button></div>
        </div>
    </section>
</div>
@endsection
