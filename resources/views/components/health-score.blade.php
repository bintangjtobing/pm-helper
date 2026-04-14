<div class="flex items-center gap-2.5">
    <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0
        @if($color === 'success') bg-green-100 dark:bg-green-900/40 @elseif($color === 'warning') bg-yellow-100 dark:bg-yellow-900/40 @else bg-red-100 dark:bg-red-900/40 @endif">
        <span class="text-xs font-bold
            @if($color === 'success') text-green-700 dark:text-green-400 @elseif($color === 'warning') text-yellow-700 dark:text-yellow-400 @else text-red-700 dark:text-red-400 @endif">
            {{ round($score) }}
        </span>
    </div>

    <div class="flex-1 min-w-[60px]">
        <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500
                @if($color === 'success') bg-green-500 @elseif($color === 'warning') bg-yellow-500 @else bg-red-500 @endif"
                style="width: {{ min($score, 100) }}%">
            </div>
        </div>
    </div>

    <span class="text-xs font-semibold tabular-nums flex-shrink-0
        @if($color === 'success') text-green-600 dark:text-green-400 @elseif($color === 'warning') text-yellow-600 dark:text-yellow-400 @else text-red-600 dark:text-red-400 @endif">
        {{ $score }}/100
    </span>
</div>
