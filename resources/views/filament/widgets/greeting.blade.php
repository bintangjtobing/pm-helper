<x-filament::widget>
    <div class="p-6 rounded-lg bg-gradient-to-r from-primary-600 to-primary-800 dark:from-primary-800 dark:to-primary-950">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-2xl">{{ $emoji }}</span>
                    <h2 class="text-xl font-bold text-white">{{ $greeting }}, {{ $userName }}!</h2>
                </div>

                @if($quote)
                <div class="mt-3">
                    <p class="text-sm italic text-white/80 leading-relaxed">
                        "{{ $quote->quote }}"
                    </p>
                    @if($quote->author)
                    <p class="mt-1.5 text-xs text-white/60">
                        — {{ $quote->author }}
                    </p>
                    @endif
                </div>
                @endif
            </div>

            <div class="text-right shrink-0">
                <p class="text-sm font-medium text-white/90">{{ now()->format('l') }}</p>
                <p class="text-xs text-white/60">{{ now()->format('d F Y') }}</p>
            </div>
        </div>
    </div>
</x-filament::widget>
