@extends('layouts.public')

@section('title', 'Medora | Care that feels connected')
@section('page', 'home')

@section('content')

    <section class="relative isolate min-h-[720px] overflow-hidden bg-slate-950 pt-28 lg:min-h-[760px]">

        <div id="homeHeroSlides" class="absolute inset-0" aria-hidden="true">
            <div
                data-home-hero-slide
                class="home-hero-slide is-active absolute inset-0 bg-cover bg-center bg-no-repeat"
                style="background-image: url('{{ asset('images/home-hero.png') }}');"
            ></div>
            <div
                data-home-hero-slide
                class="home-hero-slide absolute inset-0 bg-cover bg-center bg-no-repeat"
                style="background-image: url('{{ asset('images/home-hero-team.png') }}');"
            ></div>
            <div
                data-home-hero-slide
                class="home-hero-slide absolute inset-0 bg-cover bg-center bg-no-repeat"
                style="background-image: url('{{ asset('images/home-hero-reception.png') }}');"
            ></div>
        </div>

        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(2,6,23,0.98)_0%,rgba(2,6,23,0.88)_38%,rgba(2,6,23,0.35)_68%,rgba(2,6,23,0.15)_100%)]" aria-hidden="true"></div>
        <div class="absolute -left-32 top-28 h-80 w-80 rounded-full bg-cyan-400/15 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto flex max-w-7xl px-5 pb-20 pt-16 sm:px-8 lg:px-10 lg:pb-24 lg:pt-24">

            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100/15 bg-white/10 px-3 py-1.5 text-xs font-semibold text-cyan-50 shadow-lg backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_0_4px_rgba(103,232,249,0.15)]"></span>
                    <span id="homeHeroEyebrow">One connected place for care</span>
                </div>

                <h1 class="mt-7 max-w-xl text-5xl font-black leading-[0.98] tracking-[-0.055em] text-white sm:text-6xl lg:text-7xl">
                    <span id="homeHeroTitle">Care that feels</span>
                    <span id="homeHeroAccent" class="text-cyan-200">connected.</span>
                </h1>

                <p id="homeHeroDescription" class="mt-7 max-w-xl text-base leading-7 text-slate-200 sm:text-lg">
                    Medora brings patient information, clinical coordination, and hospital operations into one calm, reliable workspace—so every team can stay focused on the person in front of them.
                </p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-cyan-300 px-6 py-3.5 text-sm font-extrabold text-slate-950 shadow-xl shadow-cyan-300/25 transition hover:-translate-y-0.5 hover:bg-cyan-200"
                    >
                        Sign in to Medora <span aria-hidden="true">→</span>
                    </a>

                    <a
                        href="#care"
                        class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-bold text-white backdrop-blur transition hover:border-white/50 hover:bg-white/10"
                    >
                        Explore the platform
                    </a>
                </div>

                <div class="mt-12 grid max-w-xl grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-4 shadow-xl backdrop-blur">
                        <p class="text-xl font-black text-white">One view</p>
                        <p class="mt-1 text-xs leading-5 text-slate-300">of the care journey</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-4 shadow-xl backdrop-blur">
                        <p class="text-xl font-black text-white">Built for</p>
                        <p class="mt-1 text-xs leading-5 text-slate-300">patients and teams</p>
                    </div>
                    <div class="col-span-2 rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-4 shadow-xl backdrop-blur sm:col-span-1">
                        <p class="text-xl font-black text-white">Secure</p>
                        <p class="mt-1 text-xs leading-5 text-slate-300">authorised access</p>
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-3" aria-label="Hero story controls">
                    <div class="flex items-center gap-2">
                        <button type="button" data-home-hero-dot aria-label="Show care consultation story" aria-current="true" class="home-hero-dot is-active"></button>
                        <button type="button" data-home-hero-dot aria-label="Show clinical team story" aria-current="false" class="home-hero-dot"></button>
                        <button type="button" data-home-hero-dot aria-label="Show hospital arrival story" aria-current="false" class="home-hero-dot"></button>
                    </div>
                </div>
            </div>

        </div>

        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-950/60 to-transparent" aria-hidden="true"></div>

    </section>

    <section id="care" class="scroll-mt-8 bg-slate-50 py-20 sm:py-28">

        <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">

            <div class="max-w-2xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-cyan-700">The care platform</p>
                <h2 class="mt-4 text-3xl font-black tracking-[-0.04em] text-slate-950 sm:text-5xl">
                    Every moment of care, in better context.
                </h2>
                <p class="mt-5 text-base leading-7 text-slate-600 sm:text-lg">
                    A hospital runs on thousands of important details. Medora gives the right people a clearer way to manage them, from arrival to follow-up.
                </p>
            </div>

            <div class="mt-12 grid gap-5 lg:grid-cols-3">
                <article class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-950/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-100 text-xl">✦</div>
                    <p class="mt-8 text-xs font-extrabold uppercase tracking-[0.16em] text-cyan-700">Patient journey</p>
                    <h3 class="mt-3 text-2xl font-black tracking-[-0.03em] text-slate-950">A more human welcome.</h3>
                    <p class="mt-4 leading-7 text-slate-600">
                        Keep patient details, appointments, and care history close to the moment they are needed.
                    </p>
                </article>

                <article class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-950/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-xl">◌</div>
                    <p class="mt-8 text-xs font-extrabold uppercase tracking-[0.16em] text-sky-700">Clinical coordination</p>
                    <h3 class="mt-3 text-2xl font-black tracking-[-0.03em] text-slate-950">Clarity for each decision.</h3>
                    <p class="mt-4 leading-7 text-slate-600">
                        Support the teams behind each consultation with connected records, requests, and follow-up information.
                    </p>
                </article>

                <article class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-950/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-xl">↗</div>
                    <p class="mt-8 text-xs font-extrabold uppercase tracking-[0.16em] text-violet-700">Hospital operations</p>
                    <h3 class="mt-3 text-2xl font-black tracking-[-0.03em] text-slate-950">The whole system, moving together.</h3>
                    <p class="mt-4 leading-7 text-slate-600">
                        Give operational teams a purposeful workspace for the work that keeps care flowing every day.
                    </p>
                </article>
            </div>

        </div>

    </section>

    <section id="experience" class="scroll-mt-8 overflow-hidden bg-white py-20 sm:py-28">

        <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-2 lg:gap-20 lg:px-10">

            <div class="relative order-2 lg:order-1">
                <div class="absolute -inset-5 rounded-[2.5rem] bg-cyan-100/80 blur-2xl" aria-hidden="true"></div>
                <div class="relative overflow-hidden rounded-[2rem] bg-slate-950 shadow-2xl shadow-slate-950/15">
                    <img
                        src="{{ asset('images/home-hero-team.png') }}"
                        alt="A doctor and nurse coordinating care in a bright modern hospital"
                        class="h-[380px] w-full object-cover object-[70%_center] sm:h-[500px]"
                    >
                    <div class="absolute inset-x-5 bottom-5 rounded-2xl border border-white/20 bg-slate-950/75 p-5 text-white shadow-xl backdrop-blur sm:inset-x-7 sm:bottom-7 sm:p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-200">Designed around people</p>
                        <p class="mt-2 max-w-md text-lg font-bold leading-7 sm:text-xl">
                            Technology should make healthcare feel more present, not more distant.
                        </p>
                    </div>
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-cyan-700">A calmer experience</p>
                <h2 class="mt-4 text-3xl font-black tracking-[-0.04em] text-slate-950 sm:text-5xl">
                    Less searching. More listening.
                </h2>
                <p class="mt-5 text-base leading-7 text-slate-600 sm:text-lg">
                    The best hospital experiences are built by people. Medora is designed to remove friction around them with a consistent, focused interface across the care journey.
                </p>

                <div class="mt-8 space-y-5">
                    <div class="flex gap-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-sm font-black text-cyan-800">1</span>
                        <div>
                            <h3 class="font-extrabold text-slate-950">Purposeful access</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Each role sees the workspace and information relevant to their responsibilities.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-sm font-black text-cyan-800">2</span>
                        <div>
                            <h3 class="font-extrabold text-slate-950">A consistent rhythm</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">From administrative work to clinical context, familiar patterns make important tasks easier to complete.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-sm font-black text-cyan-800">3</span>
                        <div>
                            <h3 class="font-extrabold text-slate-950">Care stays central</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Tools are organised around the relationships and decisions that shape each patient's experience.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section>

    <section id="confidence" class="scroll-mt-8 bg-slate-950 py-20 text-white sm:py-28">

        <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">

            <div class="grid gap-12 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-cyan-200">Built with confidence</p>
                    <h2 class="mt-4 max-w-3xl text-3xl font-black tracking-[-0.045em] sm:text-5xl">
                        One system. Clearer coordination. More confidence in every handoff.
                    </h2>
                </div>

                <p class="max-w-xl text-base leading-7 text-slate-300 sm:text-lg">
                    Medora provides secure, role-aware access for authorised hospital users, creating a dependable foundation for connected day-to-day care.
                </p>
            </div>

            <div class="mt-12 grid gap-px overflow-hidden rounded-3xl border border-white/10 bg-white/10 sm:grid-cols-3">
                <div class="bg-slate-950 p-7">
                    <p class="text-2xl font-black text-cyan-200">Patient-first</p>
                    <p class="mt-3 text-sm leading-6 text-slate-400">Information is organised around the people receiving care.</p>
                </div>
                <div class="bg-slate-950 p-7">
                    <p class="text-2xl font-black text-cyan-200">Role-aware</p>
                    <p class="mt-3 text-sm leading-6 text-slate-400">Access is shaped by the work each authorised user needs to do.</p>
                </div>
                <div class="bg-slate-950 p-7">
                    <p class="text-2xl font-black text-cyan-200">Ready to grow</p>
                    <p class="mt-3 text-sm leading-6 text-slate-400">A connected foundation for the hospital workflows that matter next.</p>
                </div>
            </div>

            <div class="mt-12 flex flex-col justify-between gap-6 rounded-3xl bg-gradient-to-br from-cyan-300 to-sky-300 p-7 text-slate-950 sm:flex-row sm:items-center sm:p-9">
                <div>
                    <p class="text-2xl font-black tracking-[-0.03em]">Your Medora workspace is ready when you are.</p>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-800">Sign in with the account created for you by your hospital administrator.</p>
                </div>
                <a
                    href="{{ route('login') }}"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-slate-950 px-6 py-3.5 text-sm font-extrabold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-800"
                >
                    Go to sign in <span aria-hidden="true">→</span>
                </a>
            </div>

        </div>

    </section>

@endsection
