<x-filament::page>
    @include('filament.pages.partials.okr-hero', [
        'label' => ($hasFullAccess ?? $isSuperAdmin)
            ? ('Team OKR — All users (' . ($viewerRoleLabel ?? 'Leadership') . ' view)')
            : 'Team OKR — Your direct reports',
        'title' => $period?->name ?? 'No active period',
        'subtitle' => $period
            ? $period->start_date->format('d M Y') . ' — ' . $period->end_date->format('d M Y') . ' · ' . ucfirst($period->type)
            : 'Team performance data will appear here once a period is active.',
        'accent' => 'violet',
        'primary' => [
            'label' => 'Team Size',
            'value' => count($summaries),
            'tone' => 'default',
        ],
    ])

    @include('filament.pages.partials.okr-period-selector', ['periods' => $periods, 'periodId' => $periodId])

    @if(empty($summaries))
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800/50 p-8 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-violet-50 dark:bg-violet-500/10 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="text-[14px] font-semibold text-gray-900 dark:text-gray-100">No direct reports yet</h3>
            <p class="text-[12.5px] text-gray-500 dark:text-gray-400 mt-1.5 max-w-md mx-auto leading-relaxed">Assign users with your account as their supervisor to see their OKRs here.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($summaries as $row)
                @php
                    $u = $row['user'];
                    $pct = $row['achievement'];
                    $goals = $row['goals'];
                    $barClass = $pct >= 70 ? 'bg-emerald-500' : ($pct >= 40 ? 'bg-amber-500' : 'bg-rose-500');
                    $pctText = $pct >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($pct >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400');
                    $avatarUrl = $u->getAttributes()['avatar_url']
                        ?? ('https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&size=64&background=' . substr(md5($u->id), 0, 6) . '&color=ffffff');
                @endphp

                <details {{ $goals->count() > 0 ? 'open' : '' }} class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                    <summary class="cursor-pointer select-none px-4 py-3.5 flex items-center gap-3 flex-wrap hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                        <img src="{{ $avatarUrl }}" alt="" class="w-9 h-9 rounded-full shrink-0 ring-2 ring-gray-100 dark:ring-gray-700">

                        <div class="flex-1 min-w-[160px]">
                            <div class="text-[14px] font-semibold text-gray-900 dark:text-gray-50 leading-tight">{{ $u->name }}</div>
                            <div class="text-[11.5px] text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $u->email }}
                                @if($u->department)
                                    <span class="text-gray-400 dark:text-gray-500"> · </span>{{ $u->department->name }}
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap tabular-nums">{{ $goals->count() }} Objective{{ $goals->count() === 1 ? '' : 's' }}</div>
                            <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full {{ $barClass }} rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="text-[14px] font-bold tabular-nums {{ $pctText }} min-w-[54px] text-right">{{ number_format($pct, 1) }}%</div>
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </summary>

                    <div class="px-4 pb-4 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                        @if($goals->isEmpty())
                            <div class="rounded-lg border border-dashed border-amber-300 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 px-3 py-2.5 text-[12px] text-amber-800 dark:text-amber-200">
                                No Objectives assigned for {{ $period?->name ?? 'this period' }} yet.
                            </div>
                        @else
                            @if(abs($row['total_weight'] - 100) > 0.01)
                                <div class="mb-2 rounded-md border-l-4 border-amber-500 bg-amber-50 dark:bg-amber-500/10 px-3 py-2 text-[11.5px] text-amber-800 dark:text-amber-200">
                                    Weight allocation: {{ number_format($row['total_weight'], 2) }}% / 100%
                                </div>
                            @endif
                            @foreach($goals as $goal)
                                @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => false])
                            @endforeach
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    @endif
</x-filament::page>
