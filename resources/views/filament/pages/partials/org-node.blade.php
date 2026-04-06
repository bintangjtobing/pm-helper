@php
    $avatar = $user->getAttributes()['avatar_url']
        ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=128&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
    $levelColors = [
        4 => 'border-red-500 ring-red-500/30',
        3 => 'border-orange-500 ring-orange-500/30',
        2 => 'border-blue-500 ring-blue-500/30',
        1 => 'border-green-500 ring-green-500/30',
        0 => 'border-gray-400 ring-gray-400/30',
    ];
    $levelBg = [
        4 => 'bg-red-500',
        3 => 'bg-orange-500',
        2 => 'bg-blue-500',
        1 => 'bg-green-500',
        0 => 'bg-gray-500',
    ];
    $level = $user->position?->level ?? 0;
    $borderClass = $levelColors[$level] ?? $levelColors[0];
    $bgClass = $levelBg[$level] ?? $levelBg[0];
    $subs = $user->subordinates ?? collect();
@endphp

<div class="flex flex-col items-center">
    {{-- Node card --}}
    <div class="flex flex-col items-center">
        {{-- Avatar with ring --}}
        <div class="relative">
            <img src="{{ $avatar }}" alt="{{ $user->name }}"
                 class="w-16 h-16 rounded-full object-cover border-3 ring-2 {{ $borderClass }}"
                 style="border-width: 3px;" />
        </div>

        {{-- Name + Position badge --}}
        <div class="mt-2 text-center">
            <div class="px-3 py-1 rounded-md {{ $bgClass }} text-white text-xs font-semibold shadow-sm min-w-[80px]">
                {{ $user->position?->name ?? '—' }}
            </div>
            <div class="mt-1 text-xs font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
            @if($user->department)
            <div class="text-[10px] text-gray-400">{{ $user->department->name }}</div>
            @endif
        </div>
    </div>

    {{-- Children (subordinates) --}}
    @if($subs->isNotEmpty() && $depth < 3)
    <div class="flex flex-col items-center mt-4">
        {{-- Vertical line down --}}
        <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>

        {{-- Horizontal connector --}}
        @if($subs->count() > 1)
        <div class="h-px bg-gray-300 dark:bg-gray-600" style="width: {{ max(($subs->count() - 1) * 140, 100) }}px;"></div>
        @endif

        {{-- Children nodes --}}
        <div class="flex flex-wrap justify-center gap-6">
            @foreach($subs as $sub)
            <div class="flex flex-col items-center">
                {{-- Vertical line from connector --}}
                <div class="w-px h-4 bg-gray-300 dark:bg-gray-600"></div>
                @include('filament.pages.partials.org-node', ['user' => $sub, 'depth' => $depth + 1])
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
