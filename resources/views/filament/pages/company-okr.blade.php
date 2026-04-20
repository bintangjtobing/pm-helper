<x-filament::page>
    @php
        $companyColor = $companyAchievement >= 70 ? '#059669' : ($companyAchievement >= 40 ? '#d97706' : '#dc2626');
    @endphp

    {{-- Hero card --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:16px; background:linear-gradient(135deg,#0f766e,#059669); color:white; padding:18px 22px; border-radius:12px; margin-bottom:18px;">
        <div style="flex:1; min-width:220px;">
            <div style="font-size:13px; opacity:0.85;">Company OKR — transparent view</div>
            <div style="font-size:22px; font-weight:700; line-height:1.2; margin-top:2px;">
                {{ $period?->name ?? 'No period selected' }}
                @if($period)
                    <span style="font-size:13px; font-weight:400; opacity:0.85; margin-left:8px;">· {{ ucfirst($period->type) }}</span>
                @endif
            </div>
            @if($period)
                <div style="font-size:12.5px; opacity:0.85; margin-top:2px;">
                    {{ $period->start_date->format('d M Y') }} — {{ $period->end_date->format('d M Y') }}
                </div>
            @endif
        </div>

        <div style="text-align:right;">
            <div style="font-size:12px; opacity:0.85;">Company-level Achievement</div>
            <div style="font-size:30px; font-weight:800; line-height:1;">{{ number_format($companyAchievement, 1) }}%</div>
            <div style="font-size:11.5px; opacity:0.85;">Weighted across {{ $companyGoals->count() }} Objective{{ $companyGoals->count() === 1 ? '' : 's' }}</div>
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

    {{-- Company-level --}}
    <div style="margin-bottom:28px;">
        <h2 style="font-size:15px; font-weight:700; color:#374151; margin-bottom:10px; display:flex; align-items:center; gap:8px;" class="dark:!text-gray-200">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#8b5cf6;"></span>
            Company Objectives ({{ $companyGoals->count() }})
        </h2>
        @if($companyGoals->isEmpty())
            <div style="padding:16px; background:#f9fafb; border-radius:8px; font-size:12.5px; color:#6b7280; text-align:center;" class="dark:!bg-gray-800 dark:!text-gray-400">
                No Company-level Objectives for this period yet.
            </div>
        @else
            @foreach($companyGoals as $goal)
                @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => true])
            @endforeach
        @endif
    </div>

    {{-- Department-level --}}
    <div style="margin-bottom:28px;">
        <h2 style="font-size:15px; font-weight:700; color:#374151; margin-bottom:10px; display:flex; align-items:center; gap:8px;" class="dark:!text-gray-200">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#3b82f6;"></span>
            Department Objectives ({{ $deptGoals->flatten()->count() }})
        </h2>
        @if($deptGoals->isEmpty())
            <div style="padding:16px; background:#f9fafb; border-radius:8px; font-size:12.5px; color:#6b7280; text-align:center;" class="dark:!bg-gray-800 dark:!text-gray-400">
                No Department-level Objectives for this period yet.
            </div>
        @else
            @foreach($deptGoals as $deptId => $group)
                @php $deptName = $group->first()?->department?->name ?? 'Unassigned Department'; @endphp
                <details open style="margin-bottom:12px; background:#f9fafb; border-radius:8px; padding:8px 14px;" class="dark:!bg-gray-900/30">
                    <summary style="cursor:pointer; font-weight:600; font-size:13.5px; color:#3b82f6; padding:6px 0; user-select:none;">
                        🏢 {{ $deptName }} — {{ $group->count() }} Objective{{ $group->count() === 1 ? '' : 's' }}
                    </summary>
                    <div style="margin-top:10px;">
                        @foreach($group as $goal)
                            @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => true])
                        @endforeach
                    </div>
                </details>
            @endforeach
        @endif
    </div>

    {{-- Individual-level (public only) --}}
    <div>
        <h2 style="font-size:15px; font-weight:700; color:#374151; margin-bottom:10px; display:flex; align-items:center; gap:8px;" class="dark:!text-gray-200">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#6b7280;"></span>
            Individual Objectives — Public ({{ $individualGoals->flatten()->count() }})
        </h2>
        @if($individualGoals->isEmpty())
            <div style="padding:16px; background:#f9fafb; border-radius:8px; font-size:12.5px; color:#6b7280; text-align:center;" class="dark:!bg-gray-800 dark:!text-gray-400">
                No public Individual Objectives for this period.
            </div>
        @else
            @foreach($individualGoals as $ownerId => $group)
                @php $ownerName = $group->first()?->owner?->name ?? 'Unassigned'; @endphp
                <details style="margin-bottom:12px; background:#f9fafb; border-radius:8px; padding:8px 14px;" class="dark:!bg-gray-900/30">
                    <summary style="cursor:pointer; font-weight:600; font-size:13.5px; color:#6b7280; padding:6px 0; user-select:none;">
                        👤 {{ $ownerName }} — {{ $group->count() }} Objective{{ $group->count() === 1 ? '' : 's' }}
                    </summary>
                    <div style="margin-top:10px;">
                        @foreach($group as $goal)
                            @include('filament.pages.partials.okr-objective-card', ['goal' => $goal, 'showOwner' => false])
                        @endforeach
                    </div>
                </details>
            @endforeach
        @endif
    </div>
</x-filament::page>
