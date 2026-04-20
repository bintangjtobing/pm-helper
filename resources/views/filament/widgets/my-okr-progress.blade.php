<x-filament::widget>
    <x-filament::card>
        @php
            $pctText = $totalAchievement >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($totalAchievement >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400');
            $barClass = $totalAchievement >= 70 ? 'bg-emerald-500' : ($totalAchievement >= 40 ? 'bg-amber-500' : 'bg-rose-500');
        @endphp

        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <div class="text-[10.5px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">My OKR Progress</div>
                <div class="text-[13.5px] font-semibold text-gray-900 dark:text-gray-100 mt-0.5">{{ $period?->name ?? 'No active period' }}</div>
            </div>
            <a href="{{ \App\Filament\Pages\MyOkr::getUrl() }}"
               class="text-[11.5px] font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 no-underline whitespace-nowrap">View all →</a>
        </div>

        {{-- Overall card --}}
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-900/40 mb-3">
            <div>
                <div class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Overall</div>
                <div class="text-[22px] font-extrabold tabular-nums leading-none mt-0.5 {{ $pctText }}">{{ number_format($totalAchievement, 1) }}%</div>
            </div>
            <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full {{ $barClass }} rounded-full" style="width: {{ $totalAchievement }}%"></div>
            </div>
        </div>

        @if($pendingKrs > 0)
            <div class="rounded-md border-l-4 border-amber-500 bg-amber-50 dark:bg-amber-500/10 px-3 py-2 text-[11.5px] text-amber-800 dark:text-amber-200 mb-3">
                <strong class="font-semibold">{{ $pendingKrs }}</strong> Key Result{{ $pendingKrs === 1 ? '' : 's' }} not updated in the past week.
            </div>
        @endif

        @if($goals->isEmpty())
            <div class="text-[12.5px] text-gray-500 dark:text-gray-400 text-center py-3">No Objectives for this period.</div>
        @else
            <div class="space-y-2.5">
                @foreach($goals as $goal)
                    @php
                        $gpct = (float) $goal->achievement;
                        $gbar = $gpct >= 70 ? 'bg-emerald-500' : ($gpct >= 40 ? 'bg-amber-500' : 'bg-rose-500');
                        $gtxt = $gpct >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($gpct >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400');
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1 text-[12px]">
                            <div class="flex-1 min-w-0 truncate text-gray-700 dark:text-gray-200">
                                @if($goal->code)<strong class="font-semibold">{{ $goal->code }}</strong> · @endif{{ $goal->title }}
                            </div>
                            <div class="text-[11.5px] font-bold tabular-nums {{ $gtxt }} whitespace-nowrap">{{ number_format($gpct, 1) }}%</div>
                        </div>
                        <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $gbar }} rounded-full" style="width: {{ $gpct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::card>
</x-filament::widget>
