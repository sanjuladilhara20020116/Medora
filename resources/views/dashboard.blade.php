@extends('layouts.app')

@section('title', 'Dashboard | Medora HMS')
@section('page', 'app')
@section('header', 'Dashboard')

@section('content')

<div
    id="dashboardDataRoot"
    class="app-workspace mx-auto max-w-7xl"
>

    {{-- API Error --}}
    <div
        id="dashboardError"
        class="mb-6 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    {{-- Welcome --}}
    <section
        class="page-hero p-6 text-white lg:p-8"
    >

        <div
            class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
        >

            <div>

                <p
                    class="page-hero-eyebrow"
                >
                    Hospital Administration
                </p>

                <h2
                    class="mt-2 text-2xl font-extrabold tracking-tight text-white sm:text-3xl"
                >
                    Medora Administration Dashboard
                </h2>

                <p
                    class="mt-2 max-w-2xl text-sm leading-6 text-slate-200"
                >
                    A clear, live overview of your hospital operations, teams, and care activity.
                </p>

            </div>


        </div>

    </section>


    {{-- Core Statistics --}}
    @php

        $coreCards = [
            [
                'id' => 'statTotalUsers',
                'label' => 'Total Users',
                'description' => 'Registered HMS accounts',
            ],

            [
                'id' => 'statActiveUsers',
                'label' => 'Active Users',
                'description' => 'Currently enabled accounts',
            ],

            [
                'id' => 'statActiveRoles',
                'label' => 'Active Roles',
                'description' => 'Configured access roles',
            ],

            [
                'id' => 'statDepartments',
                'label' => 'Departments',
                'description' => 'Active hospital departments',
            ],
        ];

    @endphp


    <section
        class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
    >

        @foreach($coreCards as $card)

            <article
                class="metric-card p-5"
            >

                <div
                    class="metric-card-icon mb-5"
                >
                    @if ($loop->index === 0)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                    @elseif ($loop->index === 1)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                    @elseif ($loop->index === 2)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M12 3v18M3 12h18M5.6 5.6l12.8 12.8M18.4 5.6 5.6 18.4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M8 10h.01M16 10h.01" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                    @endif

                </div>

                <p
                    id="{{ $card['id'] }}"
                    class="text-3xl font-bold tracking-tight text-slate-950"
                >
                    —
                </p>

                <h3
                    class="mt-2 text-sm font-semibold text-slate-900"
                >
                    {{ $card['label'] }}
                </h3>

                <p
                    class="mt-1 text-xs text-slate-500"
                >
                    {{ $card['description'] }}
                </p>

            </article>

        @endforeach

    </section>


    {{-- HMS Module Statistics --}}
    <section class="mt-8">

        <div class="workspace-heading mb-4">

            <h3
                class="text-lg font-bold text-slate-950"
            >
                Operational activity
            </h3>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                Live totals from every connected care module.
            </p>

        </div>


        @php

            $moduleCards = [
                ['key' => 'patients', 'label' => 'Patients'],
                ['key' => 'doctors', 'label' => 'Doctors'],
                ['key' => 'appointments', 'label' => 'Appointments'],
                ['key' => 'lab_requests', 'label' => 'Lab Requests'],
                ['key' => 'medicines', 'label' => 'Medicines'],
                ['key' => 'invoices', 'label' => 'Invoices'],
            ];

        @endphp


        <div
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
        >

            @foreach($moduleCards as $module)

                <article
                class="metric-card p-5"
                >

                    <div
                        class="flex items-start justify-between"
                    >

                        <div>

                            <p
                                class="text-sm font-semibold text-slate-600"
                            >
                                {{ $module['label'] }}
                            </p>

                            <p
                                id="module-{{ $module['key'] }}-value"
                                class="mt-2 text-3xl font-bold text-slate-950"
                            >
                                —
                            </p>

                        </div>

                        <div class="metric-card-icon">
                            <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                        </div>

                    </div>


                    <p
                        id="module-{{ $module['key'] }}-status"
                        class="mt-4 text-xs font-medium text-slate-400"
                    >
                        Checking module...
                    </p>

                </article>

            @endforeach

        </div>

    </section>


    {{-- Bottom Dashboard --}}
    <section
        class="mt-8 grid gap-6 xl:grid-cols-2"
    >

        {{-- Role Distribution --}}
        <article
            class="workspace-panel p-6"
        >

            <div class="mb-6">

                <h3
                    class="font-bold text-slate-950"
                >
                    User Role Distribution
                </h3>

                <p
                    class="mt-1 text-sm text-slate-500"
                >
                    Number of registered users assigned to each role.
                </p>

            </div>

            <div
                id="roleDistribution"
                class="space-y-5"
            >

                <p
                    class="text-sm text-slate-400"
                >
                    Loading...
                </p>

            </div>

        </article>


        {{-- Recent Logins --}}
        <article
            class="workspace-panel p-6"
        >

            <div class="mb-6">

                <h3
                    class="font-bold text-slate-950"
                >
                    Recent User Activity
                </h3>

                <p
                    class="mt-1 text-sm text-slate-500"
                >
                    Most recent successful HMS logins.
                </p>

            </div>


            <div
                id="recentLogins"
                class="divide-y divide-slate-100"
            >

                <p
                    class="py-4 text-sm text-slate-400"
                >
                    Loading...
                </p>

            </div>

        </article>

    </section>

</div>

@endsection
