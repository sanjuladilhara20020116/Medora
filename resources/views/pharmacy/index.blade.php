@extends('layouts.app')

@section('title', 'Pharmacy | Medora HMS')
@section('page', 'app')
@section('header', 'Pharmacy')

@section('content')

<div id="pharmacyIndexPage" class="app-workspace mx-auto max-w-7xl">
    <div class="workspace-heading flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h2 class="text-2xl font-bold text-slate-950">Pharmacy Management</h2><p class="mt-1 text-sm text-slate-500">Manage medicine inventory, batch stock, expiry alerts and prescription dispensing.</p></div>
        <div class="flex flex-wrap gap-3"><a href="{{ route('pharmacy.catalogue') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold">Medicine Catalogue</a><a href="{{ route('pharmacy.stock') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold">Receive Stock</a><a href="{{ route('pharmacy.prescriptions') }}" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white">Process Prescriptions</a></div>
    </div>
    <div id="pharmacyError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
    <section class="grid gap-4 sm:grid-cols-3"><article class="metric-card p-5"><p class="text-sm font-semibold text-red-700">Expired Batches</p><p id="expiredStockCount" class="mt-3 text-3xl font-extrabold text-red-900">—</p></article><article class="metric-card metric-card--amber p-5"><p class="text-sm font-semibold text-amber-700">Expiring in 30 Days</p><p id="expiringStockCount" class="mt-3 text-3xl font-extrabold text-amber-900">—</p></article><article class="metric-card metric-card--sky p-5"><p class="text-sm font-semibold text-cyan-700">Low Stock Medicines</p><p id="lowStockCount" class="mt-3 text-3xl font-extrabold text-cyan-900">—</p></article></section>
    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        @foreach([['expiredStocks','Expired Batches','red'],['expiringStocks','Expiring Batches','amber'],['lowStockMedicines','Low Stock','cyan']] as [$id,$title,$colour])
        <article class="workspace-panel p-6"><h3 class="font-bold text-slate-950">{{ $title }}</h3><div id="{{ $id }}" class="mt-5 space-y-3 text-sm text-slate-500">Loading...</div></article>
        @endforeach
    </section>
</div>

@endsection
