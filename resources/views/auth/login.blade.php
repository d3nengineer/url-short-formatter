<x-guest-layout>
    <h1 class="text-2xl font-semibold">Sign in</h1>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-zinc-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-2 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-zinc-700">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900">
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-zinc-700">
            <input type="checkbox" name="remember" value="1" class="rounded border-zinc-300 text-zinc-950 focus:ring-zinc-900">
            Remember me
        </label>

        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('register') }}" class="text-sm text-zinc-600 underline hover:text-zinc-950">
                Create an account
            </a>

            <button type="submit" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                Log in
            </button>
        </div>
    </form>
</x-guest-layout>
