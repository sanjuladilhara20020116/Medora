@extends('layouts.app')

@section('title', 'Dashboard | Medora HMS')
@section('page', 'app')
@section('header', 'Dashboard')

@section('content')

<div class="mx-auto max-w-7xl">

    {{-- Welcome --}}
    <section
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >

        <div
            class="grid gap-8 p-6 md:p-8 lg:grid-cols-[1fr_auto] lg:items-center"
        >

            <div>

                <p
                    class="mb-2 text-sm font-semibold text-cyan-700"
                >
                    Hospital Administration
                </p>

                <h2
                    class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"
                >
                    Welcome to Medora HMS
                </h2>

                <p
                    class="mt-3 max-w-2xl text-sm leading-6 text-slate-500"
                >
                    Your secure workspace for managing hospital operations,
                    clinical workflows and administrative services.
                </p>

            </div>


            <div
                class="rounded-2xl bg-slate-950 px-6 py-5 text-white"
            >

                <p
                    class="text-xs uppercase tracking-wider text-slate-400"
                >
                    System Status
                </p>

                <div
                    class="mt-2 flex items-center gap-2"
                >

                    <span
                        class="h-2.5 w-2.5 rounded-full bg-emerald-400"
                    ></span>

                    <span class="font-semibold">
                        Operational
                    </span>

                </div>

            </div>

        </div>

    </section>


    {{-- Foundation Modules --}}
    <section class="mt-8">

        <div class="mb-4">

            <h3
                class="font-bold text-slate-900"
            >
                Hospital Modules
            </h3>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                Module development begins after the frontend foundation.
            </p>

        </div>


        <div
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >

            @foreach([
                ['Patients', 'Patient registration and records'],
                ['Doctors', 'Doctors and departments'],
                ['Appointments', 'Scheduling and consultations'],
                ['Billing', 'Invoices and payments'],
            ] as [$title, $description])

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >

                    <div
                        class="mb-4 h-10 w-10 rounded-xl bg-cyan-50"
                    ></div>

                    <h4
                        class="font-semibold text-slate-900"
                    >
                        {{ $title }}
                    </h4>

                    <p
                        class="mt-2 text-sm leading-5 text-slate-500"
                    >
                        {{ $description }}
                    </p>

                    <p
                        class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-400"
                    >
                        Coming next
                    </p>

                </div>

            @endforeach

        </div>

    </section>

</div>

@endsection