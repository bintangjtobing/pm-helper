<x-filament::widget>
    <div class="rounded-lg bg-gradient-to-r from-primary-600 to-primary-800 dark:from-primary-800 dark:to-primary-950" style="padding:12px 16px;">
        <div class="flex items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
                <h2 class="text-base font-bold text-white">{{ $greeting }}, {{ $userName }}!</h2>
                @if($quote)
                <p class="mt-1 text-xs italic text-white/70 leading-relaxed truncate">
                    "{{ Str::limit($quote->quote, 120) }}"
                    @if($quote->author)
                    <span class="not-italic text-white/50">— {{ $quote->author }}</span>
                    @endif
                </p>
                @endif
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs font-medium text-white/80">{{ now()->format('l') }}</p>
                <p class="text-[10px] text-white/50">{{ now()->format('d F Y') }}</p>
            </div>
        </div>
    </div>
</x-filament::widget>
