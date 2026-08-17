@extends('layouts.app')

@section('title', 'Invoice | Medora HMS')
@section('page', 'app')
@section('header', 'Invoice')

@section('content')
<div id="billingInvoiceShowPage" data-invoice-id="{{ $invoiceId }}" class="mx-auto max-w-5xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3"><a href="{{ route('billing.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold hover:bg-slate-50">Back to Billing</a><div class="flex gap-3"><button id="billingPrintInvoice" type="button" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold hover:bg-slate-50">Print invoice</button><button id="billingCancelInvoice" type="button" class="hidden rounded-xl border border-red-300 px-5 py-3 text-sm font-semibold text-red-700 hover:bg-red-50">Cancel invoice</button></div></div>
    <div id="billingInvoiceShowError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
    <div id="billingInvoiceDocument" class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><p class="text-slate-500">Loading invoice...</p></section>
    </div>
    <section id="billingPaymentSection" class="mt-6 hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h3 class="font-bold text-slate-950">Record payment</h3><p class="mt-1 text-sm text-slate-500">Payments are applied to the outstanding balance only.</p><form id="billingPaymentForm" class="mt-5 grid gap-4 md:grid-cols-2"><label class="block text-sm font-semibold text-slate-700">Amount<input name="amount" type="number" min="0.01" step="0.01" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label><label class="block text-sm font-semibold text-slate-700">Method<select name="payment_method" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"><option value="CASH">Cash</option><option value="CARD">Card</option><option value="BANK_TRANSFER">Bank transfer</option><option value="ONLINE">Online</option><option value="OTHER">Other</option></select></label><label class="block text-sm font-semibold text-slate-700">Reference <span class="font-normal text-slate-400">(optional)</span><input name="reference_number" maxlength="100" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label><label class="block text-sm font-semibold text-slate-700">Payment date and time<input name="paid_at" type="datetime-local" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label><label class="block text-sm font-semibold text-slate-700 md:col-span-2">Notes <span class="font-normal text-slate-400">(optional)</span><textarea name="notes" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></textarea></label><div class="flex justify-end md:col-span-2"><button type="submit" class="rounded-xl bg-cyan-600 px-6 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Record Payment</button></div></form></section>
</div>
@endsection
