<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
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

    {{-- Mobile Overlay --}}
    <div
        id="sidebarBackdrop"
        class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
    ></div>


    {{-- Sidebar --}}
    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-slate-950 text-white transition-transform duration-300 lg:translate-x-0"
    >

        {{-- Logo --}}
        <div
            class="flex h-20 items-center border-b border-white/10 px-6"
        >

            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400 font-bold text-slate-950"
            >
                M
            </div>

            <div class="ml-3">

                <p class="font-bold">
                    Medora
                </p>

                <p class="text-xs text-slate-400">
                    Hospital Management
                </p>

            </div>

        </div>


        {{-- Navigation --}}
        <nav
            class="flex-1 overflow-y-auto px-4 py-6"
        >

            <p
                class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500"
            >
                Workspace
            </p>

            <a
                href="{{ route('dashboard') }}"
                class="mb-1 flex items-center rounded-xl bg-cyan-400/10 px-3 py-3 text-sm font-semibold text-cyan-300"
            >
                <span
                    class="mr-3 h-2 w-2 rounded-full bg-cyan-400"
                ></span>

                Dashboard
            </a>


            @php
                $futureModules = [
                    'Patients',
                    'Doctors',
                    'Appointments',
                    'Medical Records',
                    'Laboratory',
                    'Pharmacy',
                    'Billing & Payments',
                    'Staff',
                    'Reports & Analytics',
                ];
            @endphp

            @foreach($futureModules as $module)

                <div
                    class="mb-1 flex items-center justify-between rounded-xl px-3 py-3 text-sm text-slate-400"
                >

                    <div class="flex items-center">

                        <span
                            class="mr-3 h-2 w-2 rounded-full bg-slate-700"
                        ></span>

                        {{ $module }}

                    </div>

                    <span
                        class="rounded-md bg-white/5 px-2 py-1 text-[10px] text-slate-500"
                    >
                        Soon
                    </span>

                </div>

            @endforeach

        </nav>


        {{-- Sidebar Footer --}}
        <div
            class="border-t border-white/10 p-4"
        >

            <div
                class="rounded-xl bg-white/5 px-4 py-3"
            >

                <p class="text-xs font-semibold text-slate-300">
                    Secure Session
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Protected by JWT authentication
                </p>

            </div>

        </div>

    </aside>


    {{-- Main Application --}}
    <div class="lg:pl-72">

        {{-- Header --}}
        <header
            class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-5 backdrop-blur lg:px-8"
        >

            <div class="flex items-center">

                <button
                    id="mobileMenuButton"
                    type="button"
                    class="mr-4 rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden"
                    aria-label="Open navigation"
                >
                    ☰
                </button>

                <div>

                    <p
                        class="text-xs font-medium uppercase tracking-wider text-slate-400"
                    >
                        Medora HMS
                    </p>

                    <h1
                        class="font-bold text-slate-950"
                    >
                        @yield('header', 'Dashboard')
                    </h1>

                </div>

            </div>


            {{-- User --}}
            <div
                class="flex items-center gap-4"
            >

                <div class="hidden text-right sm:block">

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

                <div
                    id="userAvatar"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white"
                >
                    M
                </div>

                <button
                    id="logoutButton"
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                >
                    Logout
                </button>

            </div>

        </header>


        {{-- Page Content --}}
        <main
            class="p-5 lg:p-8"
        >

            @yield('content')

        </main>

    </div>

</div>

</body>

</html>