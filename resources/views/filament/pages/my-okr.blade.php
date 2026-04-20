<x-filament::page>
    @php
        $user = auth()->user();
        $overallColor = $overallAchievement >= 70 ? '#059669' : ($overallAchievement >= 40 ? '#d97706' : '#dc2626');
    @endphp

    {{-- Header card: summary + period selector --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:16px; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:white; padding:18px 22px; border-radius:12px; margin-bottom:18px;">
        <div style="flex:1; min-width:220px;">
            <div style="font-size:13px; opacity:0.8;">Good to see you, {{ $user->name }}</div>
            <div style="font-size:22px; font-weight:700; line-height:1.2; margin-top:2px;">
                {{ $period?->name ?? 'No period selected' }}
                @if($period)
                    <span style="font-size:13px; font-weight:400; opacity:0.8; margin-left:8px;">· {{ ucfirst($period->type) }}</span>
                @endif
            </div>
            @if($period)
                <div style="font-size:12.5px; opacity:0.85; margin-top:2px;">
                    {{ $period->start_date->format('d M Y') }} — {{ $period->end_date->format('d M Y') }}
                </div>
            @endif
        </div>

        <div style="text-align:right;">
            <div style="font-size:12px; opacity:0.8;">Your Overall Achievement</div>
            <div style="font-size:30px; font-weight:800; line-height:1;">{{ number_format($overallAchievement, 1) }}%</div>
            <div style="font-size:11.5px; opacity:0.85;">Total weight allocated: {{ number_format($totalWeight, 1) }}%</div>
        </div>
    </div>

    {{-- Period selector --}}
    <div style="margin-bottom:18px; display:flex; align-items:center; gap:12px;">
        <label style="font-size:13px; color:#6b7280; font-weight:500;">Period:</label>
        <select wire:model="periodId"
                style="font-size:13px; padding:6px 10px; border-radius:6px; border:1px solid #d1d5db; background:white; min-width:200px;"
                class="dark:!bg-gray-800 dark:!border-gray-700 dark:!text-gray-200">
            @foreach($periods as $p)
                <option value="{{ $p->id }}">{{ $p->name }} ({{ ucfirst($p->status) }})</option>
            @endforeach
        </select>
    </div>

    {{-- Goals tree --}}
    @if($goals->isEmpty())
        <div style="padding:28px; text-align:center; background:#fef3c7; border:1px solid #fde68a; border-radius:10px;">
            <div style="font-size:32px; margin-bottom:6px;">🎯</div>
            <div style="font-size:14px; font-weight:600; color:#78350f;">No Objectives assigned for this period</div>
            <div style="font-size:12.5px; color:#92400e; margin-top:6px;">Talk to your manager to set up your OKRs, or wait until {{ $period?->name ?? 'the next period' }} opens.</div>
        </div>
    @else
        @foreach($goals as $goal)
            @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => false])
        @endforeach

        @if(abs($totalWeight - 100) > 0.01)
            <div style="padding:10px 14px; background:#fef3c7; border-left:4px solid #d97706; border-radius:6px; margin-top:12px; font-size:12.5px; color:#92400e;">
                ⚠️ Total Objective weight is <strong>{{ number_format($totalWeight, 2) }}%</strong> — should equal 100% for a fully allocated period. Ask your manager to adjust.
            </div>
        @endif
    @endif
</x-filament::page>
