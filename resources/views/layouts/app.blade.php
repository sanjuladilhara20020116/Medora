<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Medora Hospital Management System"
    >

    <title>
        @yield('title', 'Medora HMS')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body
    data-page="@yield('page', 'app')"
    class="bg-slate-50"
>

<div
    id="appShell"
    class="hidden min-h-screen"
>

    {{-- ========================================================= --}}
    {{-- Mobile Sidebar Overlay --}}
    {{-- ========================================================= --}}

    <div
        id="sidebarBackdrop"
        class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
    ></div>


    {{-- ========================================================= --}}
    {{-- Sidebar --}}
    {{-- ========================================================= --}}

    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col overflow-hidden bg-gradient-to-b from-slate-950 via-slate-950 to-cyan-950 text-white shadow-2xl shadow-slate-950/20 transition-transform duration-300 lg:translate-x-0"
    >

        <div class="pointer-events-none absolute -left-24 top-28 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-28 bottom-20 h-72 w-72 rounded-full bg-sky-500/10 blur-3xl"></div>

        {{-- ===================================================== --}}
        {{-- Logo --}}
        {{-- ===================================================== --}}

        <div
            class="relative flex h-24 items-center border-b border-white/10 px-6"
        >

            <a href="{{ route('home') }}" class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/10 p-1 ring-1 ring-white/15 outline-none transition hover:bg-white/15 focus-visible:ring-4 focus-visible:ring-cyan-200/50" aria-label="Go to Medora home">
                <img src="{{ asset('images/medora-logo.png') }}" alt="Medora" class="h-full w-full object-contain">
            </a>

            <div class="ml-3">

                <p class="font-extrabold tracking-tight">
                    Medora
                </p>

                <p class="text-[11px] font-medium text-cyan-100/60">
                    Connected care platform
                </p>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- Navigation --}}
        {{-- ===================================================== --}}

        <nav
            class="relative flex-1 overflow-y-auto px-4 py-6"
        >

            <p
                class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500"
            >
                Workspace
            </p>


            {{-- ================================================= --}}
            {{-- Dashboard --}}
            {{-- ================================================= --}}

            <a
                href="{{ route('dashboard') }}"
                data-nav-roles="ADMIN"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('dashboard')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >

                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('dashboard')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Dashboard

            </a>

            <a
                href="{{ route('doctor.dashboard') }}"
                data-nav-roles="DOCTOR"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('doctor.dashboard')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >
                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('doctor.dashboard')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Doctor Dashboard
            </a>

            <a
                href="{{ route('patient.portal') }}"
                data-nav-roles="PATIENT"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('patient.portal')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >
                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('patient.portal')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Patient Portal
            </a>


            {{-- ================================================= --}}
            {{-- Patients --}}
            {{-- Step 7 - Real Module --}}
            {{-- ================================================= --}}

            <a
                href="{{ route('patients.index') }}"
                data-nav-roles="ADMIN,DOCTOR,NURSE,RECEPTIONIST"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('patients.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >

                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('patients.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Patients

            </a>


            {{-- ================================================= --}}
            {{-- Departments --}}
            {{-- Step 8 - Real Module --}}
            {{-- ================================================= --}}

            <a
                href="{{ route('departments.index') }}"
                data-nav-roles="ADMIN,DOCTOR,NURSE,RECEPTIONIST"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('departments.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >

                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('departments.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Departments

            </a>


            {{-- ================================================= --}}
            {{-- Doctors --}}
            {{-- Step 8 - Real Module --}}
            {{-- ================================================= --}}

            <a
                href="{{ route('doctors.index') }}"
                data-nav-roles="ADMIN,DOCTOR,NURSE,RECEPTIONIST"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('doctors.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >

                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('doctors.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Doctors

            </a>


            {{-- ================================================= --}}
            {{-- Appointments --}}
            {{-- Step 9 - Real Module --}}
            {{-- ================================================= --}}

            <a
                href="{{ route('appointments.index') }}"
                data-nav-roles="ADMIN,DOCTOR,NURSE,RECEPTIONIST"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('appointments.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >

                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('appointments.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Appointments

            </a>

            <a
                href="{{ route('medical-records.index') }}"
                data-nav-roles="ADMIN,DOCTOR,NURSE"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('medical-records.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >
                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('medical-records.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Medical Records
            </a>

            <a
                href="{{ route('laboratory.index') }}"
                data-nav-roles="ADMIN,DOCTOR,NURSE,LAB_STAFF"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('laboratory.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >
                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('laboratory.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Laboratory
            </a>

            <a
                href="{{ route('pharmacy.index') }}"
                data-nav-roles="ADMIN,PHARMACIST"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('pharmacy.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >
                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('pharmacy.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Pharmacy
            </a>

            <a
                href="{{ route('billing.index') }}"
                data-nav-roles="ADMIN,ACCOUNTANT"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('billing.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >
                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('billing.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Billing &amp; Payments
            </a>

            <a
                href="{{ route('staff.index') }}"
                data-nav-roles="ADMIN"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('staff.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >
                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('staff.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Staff
            </a>

            <a
                href="{{ route('reports.index') }}"
                data-nav-roles="ADMIN"
                class="
                    mb-1 flex items-center rounded-xl px-3 py-3 text-sm font-medium
                    {{ request()->routeIs('reports.*')
                        ? 'bg-cyan-400/10 font-semibold text-cyan-300'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    }}
                "
            >
                <span
                    class="
                        mr-3 h-2 w-2 rounded-full
                        {{ request()->routeIs('reports.*')
                            ? 'bg-cyan-400'
                            : 'bg-slate-700'
                        }}
                    "
                ></span>

                Reports &amp; Analytics
            </a>

        </nav>

    </aside>


    {{-- ========================================================= --}}
    {{-- Main Application --}}
    {{-- ========================================================= --}}

    <div class="lg:pl-64">


        {{-- ===================================================== --}}
        {{-- Header --}}
        {{-- ===================================================== --}}

        <header
            class="sticky top-0 z-30 flex h-[4.5rem] items-center justify-between border-b border-slate-200/80 bg-white/85 px-5 shadow-sm shadow-slate-950/[0.03] backdrop-blur-xl lg:px-8"
        >

            {{-- Left Header --}}

            <div class="flex items-center">

                {{-- Mobile Menu --}}

                <button
                    id="mobileMenuButton"
                    type="button"
                    class="mr-4 rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden"
                    aria-label="Open navigation"
                >
                    ☰
                </button>


                {{-- Page Heading --}}

                <div>

                    <p
                        class="text-[10px] font-bold uppercase tracking-[0.18em] text-cyan-700"
                    >
                        Medora HMS
                    </p>

                    <h1
                        class="font-extrabold tracking-tight text-slate-950"
                    >
                        @yield('header', 'Dashboard')
                    </h1>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- Current User --}}
            {{-- ================================================= --}}

            <div
                class="flex items-center gap-4"
            >

                {{-- User Information --}}

                <div
                    class="hidden text-right sm:block"
                >

                    <p
                        id="currentUserName"
                        class="text-sm font-semibold text-slate-900"
                    >
                        Loading...
                    </p>

                    <p
                        id="currentUserRole"
                        class="text-xs text-slate-500"
                    >
                        ...
                    </p>

                </div>


                {{-- User Avatar --}}

                <div
                    id="userAvatar"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white"
                >
                    M
                </div>


                {{-- Logout --}}

                <button
                    id="logoutButton"
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:border-red-200 hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Logout
                </button>

            </div>

        </header>


        {{-- ===================================================== --}}
        {{-- Page Content --}}
        {{-- ===================================================== --}}

        <main
            class="min-h-[calc(100vh-4.5rem)] bg-[radial-gradient(circle_at_top_right,rgba(207,250,254,0.65),transparent_30%),radial-gradient(circle_at_bottom_left,rgba(224,242,254,0.48),transparent_28%),linear-gradient(180deg,#f8fafc_0%,#f1f5f9_100%)] p-5 lg:p-8"
        >

            @yield('content')

        </main>

    </div>

</div>

</body>

</html>
