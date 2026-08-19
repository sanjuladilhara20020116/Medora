@extends('layouts.app')

@section('title', 'Doctor Dashboard | Medora HMS')
@section('page', 'app')
@section('header', 'Doctor Dashboard')

@section('content')
<div id="doctorDashboardPage" class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-cyan-700">Clinical workspace</p>
            <h2 id="doctorDashboardGreeting" class="mt-1 text-2xl font-bold text-slate-950">Welcome, Doctor</h2>
            <p id="doctorDashboardSubtitle" class="mt-1 text-sm text-slate-500">Review your appointments and maintain patient medical records.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('patients.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Patient Directory</a>
            <a href="{{ route('medical-records.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">My Medical Records</a>
            <a href="{{ route('medical-records.create') }}" class="rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">+ Add Medical Record</a>
        </div>
    </div>

    <div id="doctorDashboardError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"></div>

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm"><p class="text-sm font-medium text-sky-800">Appointments Today</p><p id="doctorTodayAppointments" class="mt-2 text-3xl font-bold text-sky-950">—</p></article>
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm"><p class="text-sm font-medium text-amber-800">Awaiting Consultation</p><p id="doctorPendingAppointments" class="mt-2 text-3xl font-bold text-amber-950">—</p></article>
        <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm"><p class="text-sm font-medium text-emerald-800">My Medical Records</p><p id="doctorMedicalRecordCount" class="mt-2 text-3xl font-bold text-emerald-950">—</p></article>
        <article class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm"><p class="text-sm font-medium text-violet-800">Patients Treated</p><p id="doctorPatientsTreated" class="mt-2 text-3xl font-bold text-violet-950">—</p></article>
    </section>

    <section class="grid gap-6 xl:grid-cols-5">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-3">
            <div class="flex items-center justify-between border-b border-slate-200 p-5"><div><h3 class="font-bold text-slate-950">Upcoming Appointments</h3><p class="mt-1 text-sm text-slate-500">Your next scheduled consultations.</p></div><a href="{{ route('appointments.index') }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">View all</a></div>
            <div id="doctorUpcomingAppointments" class="divide-y divide-slate-100"></div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="border-b border-slate-200 p-5"><h3 class="font-bold text-slate-950">Recent Medical Records</h3><p class="mt-1 text-sm text-slate-500">Records created under your profile.</p></div>
            <div id="doctorRecentRecords" class="divide-y divide-slate-100"></div>
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
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
