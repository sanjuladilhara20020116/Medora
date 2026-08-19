@extends('layouts.app')

@section('title', 'Patient Portal | Medora HMS')
@section('page', 'app')
@section('header', 'Patient Portal')

@section('content')
<div id="patientPortalPage" class="mx-auto max-w-6xl">
    <div class="mb-7 rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-950 p-6 text-white shadow-xl sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-200">Your Medora space</p>
        <div class="mt-3 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="patientPortalGreeting" class="text-3xl font-black tracking-[-0.04em]">Welcome to your patient portal</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Review the profile held for your care and keep your contact and sign-in details up to date.</p>
            </div>
            <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-wider text-cyan-100/70">Patient ID</p>
                <p id="patientPortalCode" class="mt-1 font-bold text-white">—</p>
            </div>
        </div>
    </div>

    <div id="patientPortalError" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"></div>
    <div id="patientPortalSuccess" class="mb-5 hidden rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status"></div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-cyan-100 bg-cyan-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">Name</p>
            <p id="patientPortalName" class="mt-2 text-lg font-extrabold text-slate-950">—</p>
        </article>
        <article class="rounded-2xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-sky-700">Date of birth</p>
            <p id="patientPortalDob" class="mt-2 text-lg font-extrabold text-slate-950">—</p>
        </article>
        <article class="rounded-2xl border border-violet-100 bg-violet-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Blood group</p>
            <p id="patientPortalBloodGroup" class="mt-2 text-lg font-extrabold text-slate-950">—</p>
        </article>
        <article class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Account username</p>
            <p id="patientPortalUsername" class="mt-2 break-words text-lg font-extrabold text-slate-950">—</p>
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-5">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-lg text-cyan-800">✓</span>
                <div>
                    <h3 class="font-bold text-slate-950">Your protected information</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-500">This portal is limited to the patient profile connected to your account.</p>
                </div>
            </div>

            <dl class="mt-6 space-y-4 text-sm">
                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-slate-500">Gender</dt>
                    <dd id="patientPortalGender" class="mt-1 font-semibold text-slate-900">—</dd>
                </div>
                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-slate-500">Primary phone</dt>
                    <dd id="patientPortalPhone" class="mt-1 font-semibold text-slate-900">—</dd>
                </div>
                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-slate-500">Contact email</dt>
                    <dd id="patientPortalEmail" class="mt-1 break-words font-semibold text-slate-900">—</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Emergency contact</dt>
                    <dd id="patientPortalEmergency" class="mt-1 font-semibold text-slate-900">—</dd>
                </div>
            </dl>

            <div class="mt-6 rounded-xl bg-slate-50 p-4 text-xs leading-5 text-slate-500">
                Medical records, clinical notes, and other patient accounts are not available through this portal. Contact your hospital team if any read-only identity detail needs correction.
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-cyan-700">Profile & account</p>
                <h3 class="mt-2 text-xl font-bold text-slate-950">Keep your details current</h3>
                <p class="mt-1 text-sm leading-6 text-slate-500">Update your contact profile or choose a new password. Your date of birth, gender, blood group, and patient ID remain protected read-only details.</p>
            </div>

            <form id="patientPortalProfileForm" class="mt-6 grid gap-4 md:grid-cols-2">
                <label class="text-sm font-medium text-slate-700">First name<input name="first_name" required class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">Last name<input name="last_name" required class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">Sign-in email<input name="email" type="email" autocomplete="email" required class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">Primary phone<input name="phone" required autocomplete="tel" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">Alternate phone<input name="alternate_phone" autocomplete="tel" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">City<input name="city" required autocomplete="address-level2" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700 md:col-span-2">Address line 1<input name="address_line_1" required autocomplete="address-line1" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700 md:col-span-2">Address line 2<input name="address_line_2" autocomplete="address-line2" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">District<input name="district" autocomplete="address-level1" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">Postal code<input name="postal_code" autocomplete="postal-code" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700 md:col-span-2">Country<input name="country" autocomplete="country-name" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">Emergency contact name<input name="emergency_contact_name" required class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">Relationship<input name="emergency_contact_relation" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700 md:col-span-2">Emergency contact phone<input name="emergency_contact_phone" required autocomplete="tel" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>

                <div class="my-2 border-t border-slate-200 pt-5 md:col-span-2">
                    <p class="font-bold text-slate-950">Change password <span class="font-normal text-slate-500">(optional)</span></p>
                    <p class="mt-1 text-sm text-slate-500">Enter your current password only if you are setting a new one.</p>
                </div>
                <label class="text-sm font-medium text-slate-700">Current password<input name="current_password" type="password" autocomplete="current-password" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <div class="hidden md:block"></div>
                <label class="text-sm font-medium text-slate-700">New password<input name="password" type="password" minlength="8" autocomplete="new-password" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <label class="text-sm font-medium text-slate-700">Confirm new password<input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="mt-2 block w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"></label>
                <div class="pt-2 md:col-span-2"><button id="patientPortalSaveButton" type="submit" class="rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60">Save profile details</button></div>
            </form>
        </article>
    </section>
</div>
@endsection
