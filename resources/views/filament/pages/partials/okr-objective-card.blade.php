{{--
    @props: $goal (App\Models\Goal)
             $showOwner (bool) — whether to display the Owner row, default true
--}}
@php
    $achievement = (float) $goal->achievement;
    $barClass = $achievement >= 70 ? 'bg-emerald-500' : ($achievement >= 40 ? 'bg-amber-500' : 'bg-rose-500');
    $pctText = $achievement >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($achievement >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400');

    $levelMap = [
        'company'    => ['Company',    'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300'],
        'department' => ['Department', 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300'],
        'individual' => ['Individual', 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300'],
    ];
    [$levelLabel, $levelClasses] = $levelMap[$goal->level] ?? ['—', 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300'];

    $typeLabel = $goal->type === 'kpi' ? 'KPI' : 'OKR';
    $typeClasses = $goal->type === 'kpi'
        ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300'
        : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300';

    $statusMap = [
        'active'    => ['Active',    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300'],
        'achieved'  => ['Achieved',  'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300'],
        'missed'    => ['Missed',    'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300'],
        'cancelled' => ['Cancelled', 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300'],
        'draft'     => ['Draft',     'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300'],
    ];
    [$statusLabel, $statusClasses] = $statusMap[$goal->status] ?? ['Draft', 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300'];
@endphp

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 md:p-5 mb-6 md:mb-8 shadow-sm hover:shadow-md transition-shadow">
    {{-- Badges row --}}
    <div class="flex flex-wrap items-center gap-1.5 mb-2">
        <span class="px-2 py-0.5 text-[11px] font-semibold rounded {{ $levelClasses }}">{{ $levelLabel }}</span>
        <span class="px-2 py-0.5 text-[11px] font-semibold rounded {{ $typeClasses }}">{{ $typeLabel }}</span>
        <span class="px-2 py-0.5 text-[11px] font-semibold rounded {{ $statusClasses }}">{{ $statusLabel }}</span>
        @if($goal->visibility === 'private')
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">🔒 Private</span>
        @endif
    </div>

    {{-- Title row --}}
    <div class="flex items-start gap-2">
        @if($goal->code)
            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-600 dark:bg-indigo-500 text-white text-[11px] font-bold tabular-nums tracking-wide shadow-sm shrink-0 mt-0.5 whitespace-nowrap">{{ $goal->code }}</span>
        @endif
        <h3 class="flex-1 text-base md:text-[15px] font-semibold text-gray-900 dark:text-gray-50 leading-snug">{{ $goal->title }}</h3>
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 shrink-0 tabular-nums whitespace-nowrap">{{ number_format((float) $goal->weight, 1) }}%w</span>
    </div>

    {{-- Description --}}
    @if($goal->description)
        <p class="text-xs md:text-[12.5px] text-gray-600 dark:text-gray-300 mt-2 leading-relaxed">{{ $goal->description }}</p>
    @endif

    {{-- Meta row --}}
    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-[11.5px] text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ $goal->period?->name ?? '—' }}
        </span>
        @if($showOwner ?? true)
            <span class="inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ $goal->owner?->name ?? ($goal->level === 'company' ? 'Company-wide' : ($goal->level === 'department' ? ($goal->department?->name ?? 'Department') : 'Unassigned')) }}
            </span>
        @endif
        @if($goal->department && $goal->level !== 'department')
            <span class="inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                {{ $goal->department->name }}
            </span>
        @endif
    </div>

    {{-- Objective achievement bar --}}
    <div class="mt-4">
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Achievement</span>
            <span class="text-sm font-bold tabular-nums {{ $pctText }}">{{ number_format($achievement, 1) }}%</span>
        </div>
        <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full {{ $barClass }} rounded-full transition-all" style="width: {{ $achievement }}%"></div>
        </div>
    </div>

    {{-- Key Results --}}
    @if($goal->keyResults->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
            <div class="text-[10.5px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2.5">Key Results · {{ $goal->keyResults->count() }}</div>
            <div class="space-y-2">
                @foreach($goal->keyResults as $kr)
                    @php
                        $krPct = (float) $kr->progress_percent;
                        $krBar = $krPct >= 70 ? 'bg-emerald-500' : ($krPct >= 40 ? 'bg-amber-500' : 'bg-rose-500');
                        $krPctText = $krPct >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($krPct >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400');
                        $krBorder = $krPct >= 70 ? 'border-l-emerald-500' : ($krPct >= 40 ? 'border-l-amber-500' : 'border-l-rose-500');
                        $modeMap = [
                            'auto'   => ['Auto',   'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300'],
                            'hybrid' => ['Hybrid', 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300'],
                            'manual' => ['Manual', 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300'],
                        ];
                        [$modeLabel, $modeClasses] = $modeMap[$kr->progress_mode] ?? $modeMap['manual'];
                    @endphp

                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-3 border-l-4 {{ $krBorder }}">
                        <div class="flex items-start gap-2">
                            @if($kr->code)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-700 dark:bg-slate-600 text-slate-100 text-[10.5px] font-bold tabular-nums tracking-wide shadow-sm shrink-0 mt-0.5 whitespace-nowrap">{{ $kr->code }}</span>
                            @endif
                            <p class="flex-1 text-[13px] text-gray-800 dark:text-gray-100 leading-snug">{{ $kr->title }}</p>
                            <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $modeClasses }} shrink-0 whitespace-nowrap">{{ $modeLabel }}</span>
                        </div>

                        @if($kr->how_to_measure)
                            <p class="text-[11.5px] text-gray-500 dark:text-gray-400 mt-1.5 italic leading-relaxed">
                                <span class="font-medium not-italic text-gray-500 dark:text-gray-400">How:</span> {{ $kr->how_to_measure }}
                            </p>
                        @endif

                        <div class="flex flex-wrap items-center gap-2 mt-2.5">
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap tabular-nums">
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ number_format((float) $kr->current_value, 2) }}</span>{{ $kr->unit ? ' ' . $kr->unit : '' }}
                                /
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $kr->target_value !== null ? number_format((float) $kr->target_value, 2) : '—' }}</span>{{ $kr->unit ? ' ' . $kr->unit : '' }}
                            </div>
                            <div class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden min-w-[80px]">
                                <div class="h-full {{ $krBar }} rounded-full" style="width: {{ $krPct }}%"></div>
                            </div>
                            <span class="text-[11.5px] font-bold tabular-nums {{ $krPctText }} whitespace-nowrap">{{ number_format($krPct, 1) }}%</span>
                            <span class="text-[10.5px] text-gray-400 dark:text-gray-500 whitespace-nowrap tabular-nums">{{ number_format((float) $kr->weight, 1) }}%w</span>
                        </div>

                        @if($kr->alignment_note)
                            <div class="text-[10.5px] text-gray-400 dark:text-gray-500 mt-1.5 inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Aligned: {{ $kr->alignment_note }}
                            </div>
                        @endif

                        {{-- Inline "Update Progress" (only on My OKR, only for KRs the viewer owns, only manual/hybrid) --}}
                        @if(($updatable ?? false) && $kr->progress_mode !== 'auto' && $goal->owner_id === auth()->id())
                            <div class="mt-3" x-data="{ open: false, value: {{ (float) $kr->current_value }}, note: '', saving: false }">
                                <button type="button" x-show="!open" @click="open = true"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-[11.5px] font-semibold shadow-sm transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Update progress
                                </button>

                                <div x-show="open" x-cloak class="rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-3 space-y-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <label class="text-[10.5px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 whitespace-nowrap">New value</label>
                                        <input type="number" step="0.01" x-model="value"
                                               class="flex-1 min-w-[100px] max-w-[220px] px-2.5 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-[13px] font-semibold text-gray-900 dark:text-gray-100 tabular-nums focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                                        @if($kr->unit)
                                            <span class="text-[11.5px] text-gray-500 dark:text-gray-400">{{ $kr->unit }}</span>
                                        @endif
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500 tabular-nums">Target: {{ $kr->target_value !== null ? number_format((float) $kr->target_value, 2) : '—' }}{{ $kr->unit ? ' ' . $kr->unit : '' }}</span>
                                    </div>

                                    <input type="text" x-model="note" placeholder="Note (optional) — what changed this week?"
                                           class="w-full px-2.5 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-[12px] text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />

                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                :disabled="saving"
                                                @click="saving = true; $wire.updateKrProgress({{ $kr->id }}, value, note).then(() => { saving = false; open = false; note = ''; })"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-[12px] font-semibold shadow-sm transition">
                                            <svg x-show="!saving" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <svg x-show="saving" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            <span x-text="saving ? 'Saving…' : 'Save'"></span>
                                        </button>
                                        <button type="button" @click="open = false; value = {{ (float) $kr->current_value }}; note = ''"
                                                class="px-3 py-1.5 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-[12px] font-medium transition">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-4 px-3 py-2 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-lg text-[12px] text-amber-700 dark:text-amber-300">
            ⚠️ No Key Results defined yet.
        </div>
    @endif
</div>
