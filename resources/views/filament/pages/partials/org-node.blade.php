@php
    $avatar = $user->getAttributes()['avatar_url']
        ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=128&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
    $levelBg = [
        4 => '#ef4444', 3 => '#f97316', 2 => '#3b82f6', 1 => '#22c55e', 0 => '#6b7280',
    ];
    $levelBorder = [
        4 => '#ef4444', 3 => '#f97316', 2 => '#3b82f6', 1 => '#22c55e', 0 => '#6b7280',
    ];
    $level = $user->position?->level ?? 0;
    $bg = $levelBg[$level] ?? $levelBg[0];
    $border = $levelBorder[$level] ?? $levelBorder[0];
    $subs = $user->subordinates ?? collect();
    $lineColor = 'rgba(148, 163, 184, 0.5)';
@endphp

<div style="display:flex;flex-direction:column;align-items:center;">
    {{-- Node --}}
    <div style="display:flex;flex-direction:column;align-items:center;">
        <img src="{{ $avatar }}" alt="{{ $user->name }}"
             style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid {{ $border }};box-shadow:0 0 0 3px {{ $border }}33;" />
        <div style="margin-top:8px;text-align:center;">
            <div style="display:inline-block;padding:4px 12px;border-radius:6px;background:{{ $bg }};color:#fff;font-size:11px;font-weight:600;min-width:70px;">
                {{ $user->position?->name ?? '—' }}
            </div>
            <div style="margin-top:4px;font-size:12px;font-weight:600;color:#f1f5f9;">{{ $user->name }}</div>
            @if($user->department)
            <div style="font-size:10px;color:#94a3b8;">{{ $user->department->name }}</div>
            @endif
        </div>
    </div>

    {{-- Children --}}
    @if($subs->isNotEmpty() && $depth < 4)
    <div style="display:flex;flex-direction:column;align-items:center;margin-top:8px;">
        {{-- Vertical line down from parent --}}
        <div style="width:2px;height:24px;background:{{ $lineColor }};"></div>

        @if($subs->count() > 1)
        {{-- Horizontal connector bar --}}
        <div style="position:relative;height:2px;background:{{ $lineColor }};" >
            <div style="height:2px;background:{{ $lineColor }};min-width:{{ ($subs->count() - 1) * 160 }}px;"></div>
        </div>
        @endif

        {{-- Child nodes --}}
        <div style="display:flex;justify-content:center;gap:24px;">
            @foreach($subs as $sub)
            <div style="display:flex;flex-direction:column;align-items:center;">
                {{-- Vertical line down to child --}}
                <div style="width:2px;height:16px;background:{{ $lineColor }};"></div>
                @include('filament.pages.partials.org-node', ['user' => $sub, 'depth' => $depth + 1])
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
