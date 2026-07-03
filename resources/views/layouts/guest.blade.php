<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-6 py-12">
            <div class="w-full rounded-lg border border-zinc-200 bg-white p-8 shadow-sm">
                <a href="{{ route('home') }}" class="text-lg font-semibold text-zinc-950">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <div class="mt-8">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </body>
</html>
