@extends('layouts.app')

@section('title', 'Dashboard | Medora HMS')
@section('page', 'app')
@section('header', 'Dashboard')

@section('content')

<div
    id="dashboardDataRoot"
    class="mx-auto max-w-7xl"
>

    {{-- API Error --}}
    <div
        id="dashboardError"
        class="mb-6 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    ></div>


    {{-- Welcome --}}
    <section
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8"
    >

        <div
            class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
        >

            <div>

                <p
                    class="text-sm font-semibold text-cyan-700"
                >
                    Hospital Administration
                </p>

                <h2
                    class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"
                >
                    Medora Administration Dashboard
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    Live information retrieved from the Medora database.
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
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <div
                    class="mb-5 flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50"
                >

                    <div
                        class="h-3 w-3 rounded-full bg-cyan-500"
                    ></div>

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

        <div class="mb-4">

            <h3
                class="text-lg font-bold text-slate-950"
            >
                HMS Module Data
            </h3>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                Module statistics become live when their database modules are initialized.
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
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
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

                        <div
                            class="h-10 w-10 rounded-xl bg-slate-100"
                        ></div>

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
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
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
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
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
