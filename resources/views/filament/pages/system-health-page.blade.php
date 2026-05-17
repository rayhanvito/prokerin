<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($health as $service => $item)
            @php
                $status = $item['status'] ?? 'unknown';
                $classes = match ($status) {
                    'up' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                    'warning', 'unknown' => 'border-amber-200 bg-amber-50 text-amber-800',
                    default => 'border-rose-200 bg-rose-50 text-rose-800',
                };
            @endphp

            <section class="rounded border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-gray-950">
                        {{ str($service)->headline() }}
                    </h2>
                    <span class="{{ $classes }} rounded px-2 py-1 text-xs font-semibold uppercase">
                        {{ $status }}
                    </span>
                </div>

                <p class="mt-3 text-sm text-gray-600">
                    {{ $item['detail'] ?? 'No detail available' }}
                </p>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
