<x-filament::widget>
    <x-filament::card>
        @php
            $headlineColor = $totalAchievement >= 70 ? '#059669' : ($totalAchievement >= 40 ? '#d97706' : '#dc2626');
        @endphp

        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:10px;">
            <div>
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">My OKR Progress</div>
                <div style="font-size:13.5px; font-weight:600; color:#111827; margin-top:2px;" class="dark:!text-gray-100">{{ $period?->name ?? 'No active period' }}</div>
            </div>
            <a href="{{ \App\Filament\Pages\MyOkr::getUrl() }}" style="font-size:11.5px; color:#6366f1; text-decoration:none; white-space:nowrap;">View all →</a>
        </div>

        <div style="display:flex; align-items:center; gap:14px; padding:10px 12px; background:#f9fafb; border-radius:8px; margin-bottom:12px;" class="dark:!bg-gray-900/50">
            <div>
                <div style="font-size:11px; color:#6b7280;">Overall</div>
                <div style="font-size:22px; font-weight:800; line-height:1; color:{{ $headlineColor }};">{{ number_format($totalAchievement, 1) }}%</div>
            </div>
            <div style="flex:1; height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden;" class="dark:!bg-gray-700">
                <div style="width:{{ $totalAchievement }}%; height:100%; background:{{ $headlineColor }}; border-radius:4px;"></div>
            </div>
        </div>

        @if($pendingKrs > 0)
            <div style="padding:8px 12px; background:#fef3c7; border-left:3px solid #d97706; border-radius:4px; font-size:12px; color:#92400e; margin-bottom:10px;">
                ⚠️ {{ $pendingKrs }} Key Result{{ $pendingKrs === 1 ? '' : 's' }} not updated in the past week.
            </div>
        @endif

        @if($goals->isEmpty())
            <div style="font-size:12.5px; color:#6b7280; text-align:center; padding:14px;">No Objectives for this period.</div>
        @else
            @foreach($goals as $goal)
                @php
                    $pct = (float) $goal->achievement;
                    $color = $pct >= 70 ? '#059669' : ($pct >= 40 ? '#d97706' : '#dc2626');
                @endphp
                <div style="margin-bottom:10px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:12px; margin-bottom:4px;">
                        <div style="flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#374151;" class="dark:!text-gray-300">
                            @if($goal->code)<strong>{{ $goal->code }}</strong> · @endif{{ $goal->title }}
                        </div>
                        <div style="font-size:11.5px; font-weight:600; color:{{ $color }}; white-space:nowrap;">{{ number_format($pct, 1) }}%</div>
                    </div>
                    <div style="width:100%; height:5px; background:#e5e7eb; border-radius:3px; overflow:hidden;" class="dark:!bg-gray-700">
                        <div style="width:{{ $pct }}%; height:100%; background:{{ $color }}; border-radius:3px;"></div>
                    </div>
                </div>
            @endforeach
        @endif
    </x-filament::card>
</x-filament::widget>
