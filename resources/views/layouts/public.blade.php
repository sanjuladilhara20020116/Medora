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
        content="Medora brings patient care, clinical records, and hospital operations together in one secure workspace."
    >

    <title>@yield('title', 'Medora | Connected hospital care')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body data-page="@yield('page', 'public')" class="bg-slate-50 text-slate-950 selection:bg-cyan-200 selection:text-slate-950">

    <header class="absolute inset-x-0 top-0 z-30">

        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8 lg:px-10">

            <a
                href="{{ route('home') }}"
                class="group flex items-center gap-3"
                aria-label="Medora home"
            >
                <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/10 p-1 shadow-lg shadow-cyan-300/20 ring-1 ring-white/20 transition-transform duration-300 group-hover:-rotate-3 group-hover:scale-105">
                    <img src="{{ asset('images/medora-logo.png') }}" alt="" class="h-full w-full object-contain">
                </span>

                <span>
                    <span class="block text-base font-extrabold tracking-tight text-white">
                        Medora
                    </span>

                    <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-100/70">
                        Connected care
                    </span>
                </span>
            </a>

            <nav
                class="hidden items-center gap-7 text-sm font-medium text-slate-200 md:flex"
                aria-label="Public navigation"
            >
                <a href="#care" class="hover:text-cyan-200">Care platform</a>
                <a href="#experience" class="hover:text-cyan-200">Experience</a>
                <a href="#confidence" class="hover:text-cyan-200">Why Medora</a>
            </nav>

            <a
                href="{{ route('login') }}"
                class="rounded-full border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white shadow-sm backdrop-blur transition hover:border-cyan-200 hover:bg-cyan-200 hover:text-slate-950 sm:px-5"
            >
                Sign in
            </a>

        </div>

    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-slate-950 text-slate-300">

        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 lg:px-10">

            <div class="grid gap-10 border-b border-white/10 pb-10 md:grid-cols-[1.5fr_repeat(2,1fr)]">

                <div>
                    <a href="{{ route('home') }}" class="flex w-fit items-center gap-3 rounded-2xl outline-none focus-visible:ring-4 focus-visible:ring-cyan-200/50" aria-label="Go to Medora home">
                        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-2xl bg-white/10 p-1 ring-1 ring-white/15">
                            <img src="{{ asset('images/medora-logo.png') }}" alt="" class="h-full w-full object-contain">
                        </span>

                        <div>
                            <p class="font-extrabold tracking-tight text-white">Medora</p>
                            <p class="text-xs text-slate-500">Hospital Management System</p>
                        </div>
                    </a>

                    <p class="mt-5 max-w-sm text-sm leading-6 text-slate-400">
                        Thoughtfully connected tools for teams who make every patient moment matter.
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-200">Explore</p>
                    <div class="mt-4 flex flex-col gap-3 text-sm">
                        <a href="#care" class="w-fit hover:text-cyan-200">Care platform</a>
                        <a href="#experience" class="w-fit hover:text-cyan-200">Designed for people</a>
                        <a href="#confidence" class="w-fit hover:text-cyan-200">Built with confidence</a>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-200">Access Medora</p>
                    <p class="mt-4 text-sm leading-6 text-slate-400">
                        Existing patients, clinicians, and hospital teams can securely access their workspace here.
                    </p>
                    <a
                        href="{{ route('login') }}"
                        class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-white hover:text-cyan-200"
                    >
                        Sign in to your account <span aria-hidden="true">→</span>
                    </a>
                </div>

            </div>

            <div class="flex flex-col gap-3 pt-7 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ now()->year }} Medora HMS. Connected care, considered.</p>
                <p>Secure access for authorised hospital users.</p>
            </div>

        </div>

    </footer>

</body>

</html>
