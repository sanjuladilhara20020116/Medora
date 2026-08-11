@extends('layouts.guest')

@section('title', 'Sign In | Medora HMS')
@section('page', 'login')

@section('content')

<div class="min-h-screen lg:grid lg:grid-cols-2">

    {{-- Left Branding Panel --}}
    <section
        class="relative hidden overflow-hidden bg-slate-950 px-12 py-12 text-white lg:flex lg:flex-col lg:justify-between"
    >

        <div
            class="absolute -left-20 top-24 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl"
        ></div>

        <div
            class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-blue-600/10 blur-3xl"
        ></div>

        <div class="relative z-10">

            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-500 font-bold text-slate-950"
                >
                    M
                </div>

                <div>
                    <p class="text-xl font-bold">
                        Medora
                    </p>

                    <p class="text-xs tracking-[0.2em] text-slate-400">
                        HOSPITAL MANAGEMENT SYSTEM
                    </p>
                </div>

            </div>

        </div>

        <div class="relative z-10 max-w-xl">

            <p
                class="mb-5 text-sm font-semibold uppercase tracking-[0.2em] text-cyan-400"
            >
                Smarter healthcare operations
            </p>

            <h1
                class="text-4xl font-bold leading-tight xl:text-5xl"
            >
                One secure platform for modern hospital management.
            </h1>

            <p
                class="mt-6 max-w-lg text-base leading-7 text-slate-300"
            >
                Manage patients, clinical workflows, appointments,
                laboratory operations, pharmacy, billing and hospital
                administration from one centralized system.
            </p>

            <div class="mt-10 grid grid-cols-2 gap-4">

                <div
                    class="rounded-2xl border border-white/10 bg-white/5 p-4"
                >
                    <p class="font-semibold">
                        Secure Access
                    </p>

                    <p class="mt-1 text-sm text-slate-400">
                        JWT authentication and role-based authorization.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-white/10 bg-white/5 p-4"
                >
                    <p class="font-semibold">
                        Centralized Care
                    </p>

                    <p class="mt-1 text-sm text-slate-400">
                        Connected hospital workflows in a single platform.
                    </p>
                </div>

            </div>

        </div>

        <p class="relative z-10 text-sm text-slate-500">
            Medora HMS
        </p>

    </section>


    {{-- Login Panel --}}
    <main
        class="flex min-h-screen items-center justify-center px-6 py-12 sm:px-10"
    >

        <div class="w-full max-w-md">

            {{-- Mobile Logo --}}
            <div class="mb-10 flex items-center gap-3 lg:hidden">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 font-bold text-white"
                >
                    M
                </div>

                <div>
                    <p class="font-bold text-slate-950">
                        Medora
                    </p>

                    <p class="text-xs text-slate-500">
                        Hospital Management System
                    </p>
                </div>

            </div>


            <div class="mb-8">

                <p
                    class="mb-2 text-sm font-semibold text-cyan-700"
                >
                    Secure staff portal
                </p>

                <h2
                    class="text-3xl font-bold tracking-tight text-slate-950"
                >
                    Welcome back
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Enter your authorized Medora account credentials.
                </p>

            </div>


            {{-- Error Message --}}
            <div
                id="loginError"
                class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                role="alert"
            ></div>


            <form id="loginForm" class="space-y-5">

                <div>

                    <label
                        for="login"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Username or Email
                    </label>

                    <input
                        id="login"
                        name="login"
                        type="text"
                        required
                        autocomplete="username"
                        placeholder="Enter username or email"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-600 focus:ring-4 focus:ring-cyan-100"
                    >

                </div>


                <div>

                    <label
                        for="password"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Password
                    </label>

                    <div class="relative">

                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter password"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-20 text-sm outline-none transition focus:border-cyan-600 focus:ring-4 focus:ring-cyan-100"
                        >

                        <button
                            id="togglePassword"
                            type="button"
                            class="absolute inset-y-0 right-3 text-sm font-medium text-slate-500 hover:text-slate-900"
                        >
                            Show
                        </button>

                    </div>

                </div>


                <button
                    id="loginButton"
                    type="submit"
                    class="flex w-full items-center justify-center rounded-xl bg-slate-950 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-slate-950/10 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span id="loginButtonText">
                        Sign in securely
                    </span>
                </button>

            </form>


            <p
                class="mt-8 text-center text-xs leading-5 text-slate-400"
            >
                Access is restricted to authorized hospital personnel.
            </p>

        </div>

    </main>

</div>

@endsection