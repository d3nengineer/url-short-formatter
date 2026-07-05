<x-app-layout>
    <div class="space-y-6">
        <section class="rounded-lg border border-zinc-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-medium text-zinc-500">Dashboard</p>
            <h1 class="mt-2 text-3xl font-semibold text-zinc-950">Create a short link</h1>

            <form method="POST" action="{{ route('short-links.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="original_url" class="block text-sm font-medium text-zinc-700">Destination URL</label>
                    <input id="original_url" name="original_url" type="url" value="{{ old('original_url') }}" required placeholder="https://example.com/article" class="mt-2 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900">
                    @error('original_url')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                        Create link
                    </button>
                </div>
            </form>
        </section>

        @if (session('short_link.url'))
            <section class="rounded-lg border border-emerald-200 bg-emerald-50 p-6">
                <p class="text-sm font-medium text-emerald-800">Short link created</p>
                <a href="{{ session('short_link.url') }}" class="mt-2 block break-all text-lg font-semibold text-emerald-950 underline">
                    {{ session('short_link.url') }}
                </a>
            </section>
        @endif
    </div>
</x-app-layout>
