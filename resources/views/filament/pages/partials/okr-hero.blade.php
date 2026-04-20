{{--
    @props: $label (string)     e.g. "My OKR" (small uppercase micro-label)
             $title (string)     e.g. "Q2 2026" or "No period active yet"
             $subtitle (string)  e.g. "01 Apr 2026 — 30 Jun 2026"
             $accent (string)    'indigo' | 'emerald' | 'violet'
             $primary (array)    ['label' => 'Achievement', 'value' => '52.3%', 'tone' => 'emerald|amber|rose']
             $secondary (?array) optional ['label' => 'Weight allocated', 'value' => '100%']
--}}
@php
    $accentDot = match($accent ?? 'indigo') {
        'emerald' => 'bg-emerald-400',
        'violet'  => 'bg-violet-400',
        default   => 'bg-indigo-400',
    };
    $accentGlow = match($accent ?? 'indigo') {
        'emerald' => 'bg-emerald-500/25',
        'violet'  => 'bg-violet-500/25',
        default   => 'bg-indigo-500/25',
    };
    $toneText = match($primary['tone'] ?? 'indigo') {
        'emerald' => 'text-emerald-300',
        'amber'   => 'text-amber-300',
        'rose'    => 'text-rose-300',
        default   => 'text-white',
    };
@endphp

<div class="relative overflow-hidden rounded-2xl bg-slate-900 dark:bg-slate-950 text-white p-5 md:p-6 mb-5 shadow-lg ring-1 ring-white/5">
    {{-- Background layers --}}
    <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full {{ $accentGlow }} blur-3xl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 pointer-events-none" aria-hidden="true"></div>

    {{-- Content --}}
    <div class="relative flex flex-wrap items-end justify-between gap-6">
        <div class="flex-1 min-w-[220px]">
            <div class="inline-flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full {{ $accentDot }}"></span>
                <span class="text-[10.5px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $label }}</span>
            </div>
            <h1 class="text-2xl md:text-[26px] font-bold mt-1.5 leading-tight text-white">{{ $title }}</h1>
            @if(!empty($subtitle))
                <p class="text-[12.5px] text-slate-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($primary)
            <div class="text-right">
                <div class="text-[10.5px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $primary['label'] }}</div>
                <div class="text-4xl md:text-[40px] font-extrabold tabular-nums leading-none mt-1 {{ $toneText }}">{{ $primary['value'] }}</div>
                @if(!empty($secondary))
                    <div class="text-[11px] text-slate-400 mt-1.5 tabular-nums">{{ $secondary['label'] }}: <span class="text-slate-200 font-medium">{{ $secondary['value'] }}</span></div>
                @endif
            </div>
        @endisset
    </div>
</div>
