<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl flex-col px-6 py-8">
            <nav class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="font-semibold">
                    {{ config('app.name', 'USF') }}
                </a>

                @if (Route::has('login'))
                    <div class="flex items-center gap-3 text-sm">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-md bg-zinc-950 px-4 py-2 font-semibold text-white hover:bg-zinc-800">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-md border border-zinc-300 px-4 py-2 font-medium hover:bg-white">
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-md bg-zinc-950 px-4 py-2 font-semibold text-white hover:bg-zinc-800">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </nav>

            <section class="grid flex-1 items-center gap-10 py-16 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p class="text-sm font-medium uppercase text-zinc-500">URL short formatter</p>
                    <h1 class="mt-4 max-w-3xl text-5xl font-semibold leading-tight text-zinc-950">
                        A clean place to shape short links.
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-700">
                        Log in to access all the features.
                    </p>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-8 shadow-sm">
                    <h2 class="text-xl font-semibold">Start with an account</h2>
                    <p class="mt-3 text-zinc-700">
                        Register or sign in to reach the protected dashboard.
                    </p>
                </div>
            </section>
        </main>
    </body>
</html>
