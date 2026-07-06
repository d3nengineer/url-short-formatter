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

        @if (session('status'))
            <section class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
            </section>
        @endif

        <section class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500">Your links</p>
                    <h2 class="mt-1 text-2xl font-semibold text-zinc-950">Short links</h2>
                </div>
                @if ($shortLinks->total() > 0)
                    <p class="text-sm text-zinc-500">{{ $shortLinks->total() }} total</p>
                @endif
            </div>

            @if ($shortLinks->isEmpty())
                <div class="mt-6 rounded-md border border-dashed border-zinc-300 p-6 text-sm text-zinc-600">
                    No short links yet. Create your first link above.
                </div>
            @else
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                        <thead class="text-xs uppercase text-zinc-500">
                            <tr>
                                <th scope="col" class="px-3 py-3 font-semibold">Short URL</th>
                                <th scope="col" class="px-3 py-3 font-semibold">Destination</th>
                                <th scope="col" class="px-3 py-3 font-semibold">Clicks</th>
                                <th scope="col" class="px-3 py-3 font-semibold">Created</th>
                                <th scope="col" class="px-3 py-3 font-semibold">Last redirected</th>
                                <th scope="col" class="px-3 py-3 font-semibold">Status</th>
                                <th scope="col" class="px-3 py-3 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach ($shortLinks as $shortLink)
                                @php
                                    $shortUrl = url($shortLink->slug);
                                    $status = $shortLink->redirectUnavailableReason() ?? 'active';
                                @endphp
                                <tr>
                                    <td class="px-3 py-4 align-top">
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ $shortUrl }}" class="break-all font-medium text-zinc-950 underline">
                                                {{ $shortUrl }}
                                            </a>
                                            <div class="flex gap-3 text-xs text-zinc-500">
                                                <a href="{{ $shortUrl }}" target="_blank" rel="noopener noreferrer" class="font-medium text-zinc-700 underline">Open</a>
                                                <button type="button" class="font-medium text-zinc-700 underline" onclick="navigator.clipboard?.writeText('{{ $shortUrl }}')">Copy</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="max-w-md px-3 py-4 align-top">
                                        <span class="block break-words text-zinc-700">{{ $shortLink->original_url }}</span>
                                    </td>
                                    <td class="px-3 py-4 align-top text-zinc-700">{{ $shortLink->redirect_clicks_count }}</td>
                                    <td class="px-3 py-4 align-top text-zinc-700">{{ $shortLink->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-4 align-top text-zinc-700">
                                        {{ $shortLink->last_redirected_at?->format('Y-m-d H:i') ?? 'Never' }}
                                    </td>
                                    <td class="px-3 py-4 align-top">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-700' }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 align-top">
                                        <form method="POST" action="{{ route('short-links.destroy', $shortLink) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-700 underline hover:text-red-900">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $shortLinks->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
