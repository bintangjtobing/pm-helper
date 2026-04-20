<x-filament::page>
    {{-- Hero card --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:16px; background:linear-gradient(135deg,#1e3a8a,#3b82f6); color:white; padding:18px 22px; border-radius:12px; margin-bottom:18px;">
        <div style="flex:1; min-width:220px;">
            <div style="font-size:13px; opacity:0.85;">Team OKR {{ $isSuperAdmin ? '— All users (Super Admin)' : '— Your direct reports' }}</div>
            <div style="font-size:22px; font-weight:700; line-height:1.2; margin-top:2px;">
                {{ $period?->name ?? 'No period selected' }}
            </div>
            @if($period)
                <div style="font-size:12.5px; opacity:0.85; margin-top:2px;">
                    {{ $period->start_date->format('d M Y') }} — {{ $period->end_date->format('d M Y') }}
                </div>
            @endif
        </div>

        <div style="text-align:right;">
            <div style="font-size:12px; opacity:0.85;">Team Size</div>
            <div style="font-size:30px; font-weight:800; line-height:1;">{{ count($summaries) }}</div>
            <div style="font-size:11.5px; opacity:0.85;">{{ count($summaries) === 1 ? 'person' : 'people' }}</div>
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

    {{-- Leaderboard-style summary --}}
    @if(empty($summaries))
        <div style="padding:28px; text-align:center; background:#fef3c7; border:1px solid #fde68a; border-radius:10px;">
            <div style="font-size:32px; margin-bottom:6px;">👥</div>
            <div style="font-size:14px; font-weight:600; color:#78350f;">No direct reports yet</div>
            <div style="font-size:12.5px; color:#92400e; margin-top:6px;">Assign users with your account as their supervisor to see their OKRs here.</div>
        </div>
    @else
        @foreach($summaries as $row)
            @php
                $u = $row['user'];
                $pct = $row['achievement'];
                $color = $pct >= 70 ? '#059669' : ($pct >= 40 ? '#d97706' : '#dc2626');
                $goals = $row['goals'];
            @endphp

            <details {{ $goals->count() > 0 ? 'open' : '' }} style="margin-bottom:14px; background:white; border:1px solid #e5e7eb; border-radius:10px; padding:12px 16px;" class="dark:!bg-gray-800 dark:!border-gray-700">
                <summary style="cursor:pointer; user-select:none; display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:180px;">
                        <div style="font-weight:600; font-size:14px; color:#111827;" class="dark:!text-gray-100">{{ $u->name }}</div>
                        <div style="font-size:12px; color:#6b7280;">
                            {{ $u->email }}
                            @if($u->department) · {{ $u->department->name }} @endif
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="font-size:12px; color:#6b7280; white-space:nowrap;">{{ $goals->count() }} Objective{{ $goals->count() === 1 ? '' : 's' }}</div>
                        <div style="width:100px; height:6px; background:#e5e7eb; border-radius:3px; overflow:hidden;" class="dark:!bg-gray-700">
                            <div style="width:{{ $pct }}%; height:100%; background:{{ $color }};"></div>
                        </div>
                        <div style="font-size:13px; font-weight:700; color:{{ $color }}; min-width:60px; text-align:right;">{{ number_format($pct, 1) }}%</div>
                    </div>
                </summary>

                <div style="margin-top:14px;">
                    @if($goals->isEmpty())
                        <div style="padding:12px; background:#fef3c7; border-radius:6px; font-size:12.5px; color:#92400e;">
                            ⚠️ No Objectives assigned for {{ $period?->name ?? 'this period' }} yet.
                        </div>
                    @else
                        @if(abs($row['total_weight'] - 100) > 0.01)
                            <div style="padding:8px 12px; background:#fef3c7; border-left:3px solid #d97706; border-radius:4px; font-size:12px; color:#92400e; margin-bottom:10px;">
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
    @endif
</x-filament::page>
