@extends('layouts.app')

@section('title', 'Doctor Dashboard | Medora HMS')
@section('page', 'app')
@section('header', 'Doctor Dashboard')

@section('content')
<div id="doctorDashboardPage" class="app-workspace mx-auto max-w-7xl">
    <div class="page-hero mb-6 flex flex-col gap-5 p-6 text-white sm:flex-row sm:items-end sm:justify-between lg:p-8">
        <div>
            <p class="page-hero-eyebrow">Clinical workspace</p>
            <h2 id="doctorDashboardGreeting" class="mt-2 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Welcome, Doctor</h2>
            <p id="doctorDashboardSubtitle" class="mt-2 text-sm text-slate-200">Review your appointments and maintain patient medical records.</p>
        </div>
        <div class="workspace-actions flex flex-wrap gap-3">
            <a href="{{ route('patients.index') }}" class="rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur hover:bg-white/20">Patient Directory</a>
            <a href="{{ route('medical-records.index') }}" class="rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur hover:bg-white/20">My Medical Records</a>
            <a href="{{ route('medical-records.create') }}" class="rounded-xl bg-white px-4 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-slate-950/20 hover:bg-cyan-50">+ Add Medical Record</a>
        </div>
    </div>

    <div id="doctorDashboardError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"></div>

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="metric-card metric-card--sky p-5"><p class="text-sm font-semibold text-sky-800">Appointments Today</p><p id="doctorTodayAppointments" class="mt-3 text-3xl font-extrabold tracking-tight text-sky-950">—</p></article>
        <article class="metric-card metric-card--amber p-5"><p class="text-sm font-semibold text-amber-800">Awaiting Consultation</p><p id="doctorPendingAppointments" class="mt-3 text-3xl font-extrabold tracking-tight text-amber-950">—</p></article>
        <article class="metric-card metric-card--emerald p-5"><p class="text-sm font-semibold text-emerald-800">My Medical Records</p><p id="doctorMedicalRecordCount" class="mt-3 text-3xl font-extrabold tracking-tight text-emerald-950">—</p></article>
        <article class="metric-card metric-card--violet p-5"><p class="text-sm font-semibold text-violet-800">Patients Treated</p><p id="doctorPatientsTreated" class="mt-3 text-3xl font-extrabold tracking-tight text-violet-950">—</p></article>
    </section>

    <section class="grid gap-6 xl:grid-cols-5">
        <article class="workspace-panel workspace-panel--table xl:col-span-3">
            <div class="flex items-center justify-between border-b border-slate-200 p-5"><div><h3 class="font-bold text-slate-950">Upcoming Appointments</h3><p class="mt-1 text-sm text-slate-500">Your next scheduled consultations.</p></div><a href="{{ route('appointments.index') }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">View all</a></div>
            <div id="doctorUpcomingAppointments" class="divide-y divide-slate-100"></div>
        </article>

        <article class="workspace-panel workspace-panel--table xl:col-span-2">
            <div class="border-b border-slate-200 p-5"><h3 class="font-bold text-slate-950">Recent Medical Records</h3><p class="mt-1 text-sm text-slate-500">Records created under your profile.</p></div>
            <div id="doctorRecentRecords" class="divide-y divide-slate-100"></div>
        </article>
    </section>

    <section class="workspace-panel mt-6 p-6">
        <div class="mb-5"><h3 class="font-bold text-slate-950">Profile Details</h3><p class="mt-1 text-sm text-slate-500">Update your account details. Enter your current password only when choosing a new password.</p></div>
        <form id="doctorProfileForm" class="grid gap-4 md:grid-cols-2">
            <label class="text-sm font-medium text-slate-700">Full name<input name="name" required class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <label class="text-sm font-medium text-slate-700">Username<input name="username" required class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <label class="text-sm font-medium text-slate-700">Email address<input name="email" type="email" required class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <label class="text-sm font-medium text-slate-700">Phone<input name="phone" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <label class="text-sm font-medium text-slate-700">Current password<input name="current_password" type="password" autocomplete="current-password" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <div></div>
            <label class="text-sm font-medium text-slate-700">New password<input name="password" type="password" minlength="8" autocomplete="new-password" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <label class="text-sm font-medium text-slate-700">Confirm new password<input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3"></label>
            <div class="md:col-span-2"><button id="doctorProfileSaveButton" type="submit" class="rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white hover:bg-cyan-700">Save Profile Details</button></div>
        </form>
    </section>
</div>
@endsection
