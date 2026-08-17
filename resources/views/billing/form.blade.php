@extends('layouts.app')

@section('title', 'Generate Invoice | Medora HMS')
@section('page', 'app')
@section('header', 'Generate Invoice')

@section('content')
<div id="billingInvoiceFormPage" class="mx-auto max-w-5xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h2 class="text-2xl font-bold text-slate-950">Generate Invoice</h2><p class="mt-1 text-sm text-slate-500">Select the patient, add billable services, then generate the invoice.</p></div>
        <a href="{{ route('billing.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold hover:bg-slate-50">Back to Billing</a>
    </div>

    <div id="billingInvoiceFormError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

    <form id="billingInvoiceForm" class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-bold text-slate-950">Invoice details</h3>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700">Patient<select id="billingPatient" name="patient_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"><option value="">Select patient</option></select></label>
                <label class="block text-sm font-semibold text-slate-700">Due date <span class="font-normal text-slate-400">(optional)</span><input name="due_date" type="date" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div><h3 class="font-bold text-slate-950">Available service charges</h3><p class="mt-1 text-sm text-slate-500">Completed consultations, active laboratory requests, pharmacy dispensations, and recorded admissions that have not yet been invoiced.</p></div>
            <div id="billingChargesEmpty" class="mt-5 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Select a patient to load available charges.</div>
            <div id="billingChargesList" class="mt-5 space-y-3"></div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-bold text-slate-950">Manual charges</h3><p class="mt-1 text-sm text-slate-500">Use this for an admission charge not yet recorded, or another approved charge.</p></div><button id="addBillingManualItem" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50">+ Add charge</button></div>
            <div id="billingManualItems" class="mt-5 space-y-3"></div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-3"><label class="block text-sm font-semibold text-slate-700">Discount<input name="discount_amount" type="number" min="0" step="0.01" value="0" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label><label class="block text-sm font-semibold text-slate-700">Tax<input name="tax_amount" type="number" min="0" step="0.01" value="0" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label><div class="rounded-xl bg-slate-950 px-5 py-3 text-white"><p class="text-xs uppercase tracking-wider text-slate-400">Invoice total</p><p id="billingInvoiceTotal" class="mt-1 text-2xl font-bold">LKR 0.00</p></div></div>
            <label class="mt-4 block text-sm font-semibold text-slate-700">Notes <span class="font-normal text-slate-400">(optional)</span><textarea name="notes" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" placeholder="Payment terms or notes for the patient"></textarea></label>
            <div class="mt-6 flex justify-end"><button type="submit" class="rounded-xl bg-cyan-600 px-6 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Generate Invoice</button></div>
        </section>
    </form>
</div>
@endsection
