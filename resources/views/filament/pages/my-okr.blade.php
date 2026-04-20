<x-filament::page>
    @php
        $user = auth()->user();
        $primaryTone = $overallAchievement >= 70 ? 'emerald' : ($overallAchievement >= 40 ? 'amber' : 'rose');
    @endphp

    @include('filament.pages.partials.okr-hero', [
        'label' => 'My OKR — ' . $user->name,
        'title' => $period?->name ?? 'No active period',
        'subtitle' => $period
            ? $period->start_date->format('d M Y') . ' — ' . $period->end_date->format('d M Y') . ' · ' . ucfirst($period->type)
            : 'Set up a period to start tracking your Objectives.',
        'accent' => 'indigo',
        'primary' => [
            'label' => 'Overall Achievement',
            'value' => number_format($overallAchievement, 1) . '%',
            'tone' => $primaryTone,
        ],
        'secondary' => [
            'label' => 'Weight allocated',
            'value' => number_format($totalWeight, 1) . '%',
        ],
    ])

    @include('filament.pages.partials.okr-period-selector', ['periods' => $periods, 'periodId' => $periodId])

    @if($goals->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800/50 p-8 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-[14px] font-semibold text-gray-900 dark:text-gray-100">No Objectives assigned for this period</h3>
            <p class="text-[12.5px] text-gray-500 dark:text-gray-400 mt-1.5 max-w-md mx-auto leading-relaxed">Talk to your manager to set up your OKRs for {{ $period?->name ?? 'the next period' }}, or wait until a new period opens.</p>
        </div>
    @else
        <div class="space-y-6 md:space-y-8">
            @foreach($goals as $goal)
                @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => false, 'updatable' => true])
            @endforeach
        </div>

        @if(abs($totalWeight - 100) > 0.01)
            <div class="mt-3 rounded-lg border-l-4 border-amber-500 bg-amber-50 dark:bg-amber-500/10 px-4 py-3 text-[12.5px] text-amber-800 dark:text-amber-200">
                <strong class="font-semibold">Weight allocation: {{ number_format($totalWeight, 2) }}% / 100%</strong>
                — should equal 100% for a fully allocated period. Ask your manager to adjust.
            </div>
        @endif
    @endif
</x-filament::page>
