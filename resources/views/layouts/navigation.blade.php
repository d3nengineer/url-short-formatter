<nav class="border-b border-zinc-200 bg-white">
    <div class="mx-auto flex h-16 w-full max-w-5xl items-center justify-between px-6">
        <a href="{{ route('dashboard') }}" class="font-semibold text-zinc-950">
            {{ config('app.name', 'Laravel') }}
        </a>

        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('dashboard') }}" class="text-zinc-700 hover:text-zinc-950">
                Dashboard
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-md border border-zinc-300 px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-50">
                    Log out
                </button>
            </form>
        </div>
    </div>
</nav>
