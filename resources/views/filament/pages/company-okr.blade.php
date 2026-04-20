<x-filament::page>
    @php
        $primaryTone = $companyAchievement >= 70 ? 'emerald' : ($companyAchievement >= 40 ? 'amber' : 'rose');
    @endphp

    @include('filament.pages.partials.okr-hero', [
        'label' => 'Company OKR — Transparent View',
        'title' => $period?->name ?? 'No active period',
        'subtitle' => $period
            ? $period->start_date->format('d M Y') . ' — ' . $period->end_date->format('d M Y') . ' · ' . ucfirst($period->type)
            : 'Company OKR data will appear here once a period is configured.',
        'accent' => 'emerald',
        'primary' => [
            'label' => 'Company Achievement',
            'value' => number_format($companyAchievement, 1) . '%',
            'tone' => $primaryTone,
        ],
        'secondary' => [
            'label' => 'Company Objectives',
            'value' => $companyGoals->count(),
        ],
    ])

    @include('filament.pages.partials.okr-period-selector', ['periods' => $periods, 'periodId' => $periodId])

    {{-- Company-level --}}
    <section class="mb-8">
        <div class="flex items-center gap-2 mb-3">
            <span class="h-2 w-2 rounded-full bg-violet-500"></span>
            <h2 class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Company Objectives</h2>
            <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 tabular-nums">· {{ $companyGoals->count() }}</span>
        </div>

        @if($companyGoals->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-5 text-center text-[12.5px] text-gray-500 dark:text-gray-400">
                No Company-level Objectives for this period yet.
            </div>
        @else
            @foreach($companyGoals as $goal)
                @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => true])
            @endforeach
        @endif
    </section>

    {{-- Department-level --}}
    <section class="mb-8">
        <div class="flex items-center gap-2 mb-3">
            <span class="h-2 w-2 rounded-full bg-sky-500"></span>
            <h2 class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Department Objectives</h2>
            <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 tabular-nums">· {{ $deptGoals->flatten()->count() }}</span>
        </div>

        @if($deptGoals->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-5 text-center text-[12.5px] text-gray-500 dark:text-gray-400">
                No Department-level Objectives for this period yet.
            </div>
        @else
            @foreach($deptGoals as $deptId => $group)
                @php $deptName = $group->first()?->department?->name ?? 'Unassigned Department'; @endphp
                <details open class="mb-3 rounded-xl bg-white dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <summary class="cursor-pointer select-none px-4 py-3 flex items-center gap-2 text-[13px] font-semibold text-sky-700 dark:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-500/10 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                        <span class="flex-1">{{ $deptName }}</span>
                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 tabular-nums">{{ $group->count() }} Objective{{ $group->count() === 1 ? '' : 's' }}</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-4 pb-4 pt-2">
                        @foreach($group as $goal)
                            @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => true])
                        @endforeach
                    </div>
                </details>
            @endforeach
        @endif
    </section>

    {{-- Individual-level (public) --}}
    <section>
        <div class="flex items-center gap-2 mb-3">
            <span class="h-2 w-2 rounded-full bg-slate-500"></span>
            <h2 class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Individual Objectives — Public</h2>
            <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 tabular-nums">· {{ $individualGoals->flatten()->count() }}</span>
        </div>

        @if($individualGoals->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-5 text-center text-[12.5px] text-gray-500 dark:text-gray-400">
                No public Individual Objectives for this period.
            </div>
        @else
            @foreach($individualGoals as $ownerId => $group)
                @php $ownerName = $group->first()?->owner?->name ?? 'Unassigned'; @endphp
                <details class="mb-3 rounded-xl bg-white dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <summary class="cursor-pointer select-none px-4 py-3 flex items-center gap-2 text-[13px] font-semibold text-gray-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-slate-500/10 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="flex-1">{{ $ownerName }}</span>
                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 tabular-nums">{{ $group->count() }} Objective{{ $group->count() === 1 ? '' : 's' }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-4 pb-4 pt-2">
                        @foreach($group as $goal)
                            @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => false])
                        @endforeach
                    </div>
                </details>
            @endforeach
        @endif
    </section>
</x-filament::page>
