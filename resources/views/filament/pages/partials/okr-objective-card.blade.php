{{--
    @props: $goal (App\Models\Goal)
             $showOwner (bool) — whether to display the Owner row
--}}
@php
    $achievement = (float) $goal->achievement;
    $barColor = $achievement >= 70 ? '#059669' : ($achievement >= 40 ? '#d97706' : '#dc2626');
    $levelBadge = [
        'company' => ['Company', 'bg-purple-500/10 text-purple-500'],
        'department' => ['Department', 'bg-blue-500/10 text-blue-500'],
        'individual' => ['Individual', 'bg-gray-500/10 text-gray-500'],
    ][$goal->level] ?? ['—', 'bg-gray-500/10 text-gray-500'];
    $typeBadge = $goal->type === 'kpi' ? 'KPI' : 'OKR';
    $statusBadge = match ($goal->status) {
        'active' => 'bg-green-500/10 text-green-500',
        'achieved' => 'bg-blue-500/10 text-blue-500',
        'missed' => 'bg-red-500/10 text-red-500',
        'cancelled' => 'bg-gray-500/10 text-gray-500',
        default => 'bg-yellow-500/10 text-yellow-600',
    };
@endphp

<div style="background:white; border:1px solid #e5e7eb; border-radius:10px; padding:16px; margin-bottom:16px;"
     class="dark:!bg-gray-800 dark:!border-gray-700">

    {{-- Header: badges + code + title --}}
    <div style="display:flex; align-items:flex-start; gap:10px; flex-wrap:wrap;">
        <span class="px-2 py-0.5 text-xs font-medium rounded {{ $levelBadge[1] }}" style="white-space:nowrap;">{{ $levelBadge[0] }}</span>
        <span class="px-2 py-0.5 text-xs font-medium rounded bg-emerald-500/10 text-emerald-500" style="white-space:nowrap;">{{ $typeBadge }}</span>
        <span class="px-2 py-0.5 text-xs font-medium rounded {{ $statusBadge }}" style="white-space:nowrap;">{{ ucfirst($goal->status) }}</span>

        @if($goal->code)
            <span style="font-weight:700; color:#111827; font-size:14px;" class="dark:!text-gray-100">{{ $goal->code }}</span>
        @endif

        <div style="flex:1; min-width:200px; font-weight:600; color:#111827; font-size:15px;" class="dark:!text-gray-100">
            {{ $goal->title }}
        </div>

        <div style="font-size:12px; color:#6b7280; white-space:nowrap;">
            Weight <strong>{{ number_format((float) $goal->weight, 1) }}%</strong>
        </div>
    </div>

    @if($goal->description)
        <div style="font-size:12.5px; color:#6b7280; margin-top:6px;" class="dark:!text-gray-400">{{ $goal->description }}</div>
    @endif

    {{-- Meta row: period + owner + department --}}
    <div style="display:flex; flex-wrap:wrap; gap:14px; margin-top:10px; font-size:11.5px; color:#6b7280;">
        <span>📅 {{ $goal->period?->name ?? '—' }}</span>
        @if($showOwner ?? true)
            <span>👤 {{ $goal->owner?->name ?? ($goal->level === 'company' ? 'Company-wide' : ($goal->level === 'department' ? ($goal->department?->name ?? 'Department') : 'Unassigned')) }}</span>
        @endif
        @if($goal->department && $goal->level !== 'department')
            <span>🏢 {{ $goal->department->name }}</span>
        @endif
        @if($goal->visibility === 'private')
            <span style="color:#dc2626;">🔒 Private</span>
        @endif
    </div>

    {{-- Objective achievement bar --}}
    <div style="margin-top:12px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
            <span style="font-size:12px; color:#6b7280; font-weight:500;" class="dark:!text-gray-400">Objective Achievement</span>
            <span style="font-size:13px; font-weight:700; color:{{ $barColor }};">{{ number_format($achievement, 1) }}%</span>
        </div>
        <div style="width:100%; height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden;" class="dark:!bg-gray-700">
            <div style="width:{{ $achievement }}%; height:100%; background:{{ $barColor }}; border-radius:4px; transition:width 0.3s;"></div>
        </div>
    </div>

    {{-- Key Results --}}
    @if($goal->keyResults->isNotEmpty())
        <div style="margin-top:16px; border-top:1px dashed #e5e7eb; padding-top:12px;" class="dark:!border-gray-700">
            <div style="font-size:11.5px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">Key Results</div>
            @foreach($goal->keyResults as $kr)
                @php
                    $krPct = (float) $kr->progress_percent;
                    $krColor = $krPct >= 70 ? '#059669' : ($krPct >= 40 ? '#d97706' : '#dc2626');
                    $modeBadge = match ($kr->progress_mode) {
                        'auto' => ['Auto', 'bg-blue-500/10 text-blue-500'],
                        'hybrid' => ['Hybrid', 'bg-purple-500/10 text-purple-500'],
                        default => ['Manual', 'bg-gray-500/10 text-gray-500'],
                    };
                @endphp
                <div style="padding:10px 12px; margin-bottom:8px; background:#f9fafb; border-radius:6px; border-left:3px solid {{ $krColor }};" class="dark:!bg-gray-900/50">
                    <div style="display:flex; align-items:flex-start; gap:10px; flex-wrap:wrap;">
                        @if($kr->code)
                            <span style="font-weight:600; color:#4b5563; font-size:12.5px;" class="dark:!text-gray-300">{{ $kr->code }}</span>
                        @endif
                        <div style="flex:1; min-width:180px; font-size:13px; color:#111827; line-height:1.4;" class="dark:!text-gray-200">{{ $kr->title }}</div>
                        <span class="px-2 py-0.5 text-xs font-medium rounded {{ $modeBadge[1] }}" style="white-space:nowrap; font-size:10.5px;">{{ $modeBadge[0] }}</span>
                    </div>

                    @if($kr->how_to_measure)
                        <div style="font-size:11.5px; color:#6b7280; margin-top:4px; font-style:italic;" class="dark:!text-gray-400">
                            📏 {{ $kr->how_to_measure }}
                        </div>
                    @endif

                    <div style="display:flex; align-items:center; gap:10px; margin-top:8px;">
                        <div style="font-size:11.5px; color:#6b7280; white-space:nowrap;" class="dark:!text-gray-400">
                            <strong style="color:#111827;" class="dark:!text-gray-200">{{ number_format((float) $kr->current_value, 2) }}</strong>
                            @if($kr->unit) {{ $kr->unit }} @endif
                            /
                            <strong style="color:#111827;" class="dark:!text-gray-200">{{ $kr->target_value !== null ? number_format((float) $kr->target_value, 2) : '—' }}</strong>
                            @if($kr->unit) {{ $kr->unit }} @endif
                        </div>
                        <div style="flex:1; height:6px; background:#e5e7eb; border-radius:3px; overflow:hidden;" class="dark:!bg-gray-700">
                            <div style="width:{{ $krPct }}%; height:100%; background:{{ $krColor }}; border-radius:3px;"></div>
                        </div>
                        <span style="font-size:11.5px; font-weight:600; color:{{ $krColor }}; white-space:nowrap;">{{ number_format($krPct, 1) }}%</span>
                        <span style="font-size:11px; color:#9ca3af; white-space:nowrap;">({{ number_format((float) $kr->weight, 1) }}%w)</span>
                    </div>

                    @if($kr->alignment_note)
                        <div style="font-size:11px; color:#9ca3af; margin-top:4px;">
                            🔗 Aligned: {{ $kr->alignment_note }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div style="margin-top:12px; padding:10px; background:#fef3c7; border-radius:6px; font-size:12px; color:#92400e;">
            ⚠️ No Key Results defined yet.
        </div>
    @endif
</div>
