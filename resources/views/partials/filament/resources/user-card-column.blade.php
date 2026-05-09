@php
    $record = $getRecord();
    $roles = $record->roles->pluck('name')->toArray();
    $roleColors = [
        'Super Admin' => 'bg-red-500/10 text-red-500 ring-red-500/20',
        'Project Manager' => 'bg-blue-500/10 text-blue-500 ring-blue-500/20',
        'Developer' => 'bg-emerald-500/10 text-emerald-500 ring-emerald-500/20',
        'QA / Tester' => 'bg-amber-500/10 text-amber-500 ring-amber-500/20',
        'DevOps' => 'bg-violet-500/10 text-violet-500 ring-violet-500/20',
        'Stakeholder' => 'bg-orange-500/10 text-orange-500 ring-orange-500/20',
    ];
    $rawAvatar = $record->getAttributes()['avatar_url'] ?? null;
    $avatarSrc = $rawAvatar ?: ('https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&size=128&background=' . substr(md5($record->id), 0, 6) . '&color=ffffff&bold=true');

    $lastSeen = $record->last_seen_at;
    if (!$lastSeen) {
        $presenceLabel = __('Never logged in');
        $presenceDot = 'bg-gray-400';
        $presenceText = 'text-gray-500 dark:text-gray-400';
    } else {
        $minutesAgo = $lastSeen->diffInMinutes(now());
        if ($minutesAgo <= 5) {
            $presenceLabel = __('Active now');
            $presenceDot = 'bg-emerald-500';
            $presenceText = 'text-emerald-600 dark:text-emerald-400';
        } elseif ($minutesAgo <= 60) {
            $presenceLabel = __('Active :n min ago', ['n' => $minutesAgo]);
            $presenceDot = 'bg-emerald-400';
            $presenceText = 'text-emerald-600 dark:text-emerald-400';
        } elseif ($lastSeen->isToday()) {
            $presenceLabel = __('Today, :time', ['time' => $lastSeen->format('H:i')]);
            $presenceDot = 'bg-amber-400';
            $presenceText = 'text-amber-600 dark:text-amber-400';
        } elseif ($lastSeen->isYesterday()) {
            $presenceLabel = __('Yesterday, :time', ['time' => $lastSeen->format('H:i')]);
            $presenceDot = 'bg-amber-500';
            $presenceText = 'text-amber-600 dark:text-amber-400';
        } else {
            $daysAgo = (int) $lastSeen->diffInDays(now());
            if ($daysAgo <= 7) {
                $presenceLabel = __(':n days ago', ['n' => $daysAgo]);
                $presenceDot = 'bg-orange-500';
                $presenceText = 'text-orange-600 dark:text-orange-400';
            } else {
                $presenceLabel = __(':n days idle', ['n' => $daysAgo]);
                $presenceDot = 'bg-red-500';
                $presenceText = 'text-red-600 dark:text-red-400';
            }
        }
    }
@endphp
<div class="flex flex-col items-center w-full px-4 py-5 text-center">
    {{-- Avatar --}}
    <div class="relative">
        <img src="{{ $avatarSrc }}" alt="{{ $record->name }}"
             class="object-cover w-20 h-20 rounded-full ring-4 ring-gray-100 dark:ring-gray-600" loading="lazy" />
        @if($record->email_verified_at)
        <div class="absolute bottom-0 right-0 flex items-center justify-center w-5 h-5 bg-green-500 rounded-full ring-2 ring-white dark:ring-gray-800" title="{{ __('Verified') }}">
            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </div>
        @endif
    </div>

    {{-- Name --}}
    <div class="mt-3 text-sm font-bold text-gray-900 dark:text-white">{{ $record->name }}</div>

    {{-- Username --}}
    <div class="mt-0.5 text-xs font-medium text-gray-400 dark:text-gray-500">{{ '@' . $record->username }}</div>

    {{-- Position & Department --}}
    @if($record->position || $record->department)
    <div class="mt-1.5">
        @if($record->position)
        <div class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $record->position->name }}</div>
        @endif
        @if($record->department)
        <div class="flex items-center justify-center gap-1 mt-0.5">
            <span style="width:6px;height:6px;border-radius:50%;background:{{ $record->department->color }};display:inline-block;"></span>
            <span class="text-[10px] text-gray-400">{{ $record->department->name }}</span>
        </div>
        @endif
    </div>
    @endif

    {{-- Email --}}
    <div class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]">{{ $record->email }}</div>

    {{-- Roles --}}
    <div class="flex flex-wrap justify-center gap-2 mt-3 mb-1">
        @foreach($roles as $role)
        <span class="px-3 py-1 text-[10px] font-semibold tracking-wide uppercase rounded-full ring-1 {{ $roleColors[$role] ?? 'bg-gray-500/10 text-gray-500 ring-gray-500/20' }}">
            {{ $role }}
        </span>
        @endforeach
    </div>

    {{-- Supervisor --}}
    @if($record->supervisor)
    <div class="mt-2 text-[10px] text-gray-400">
        {{ __('Reports to') }} <span class="font-medium text-gray-500 dark:text-gray-300">{{ $record->supervisor->name }}</span>
    </div>
    @endif

    {{-- Last Active badge --}}
    <div class="flex items-center justify-center gap-1.5 mt-2.5 px-2.5 py-1 rounded-full bg-gray-50 dark:bg-gray-800/50" title="{{ $lastSeen ? $lastSeen->format('Y-m-d H:i:s') : __('Never logged in') }}">
        <span class="inline-block w-1.5 h-1.5 rounded-full {{ $presenceDot }}"></span>
        <span class="text-[10px] font-medium {{ $presenceText }}">{{ $presenceLabel }}</span>
    </div>

    {{-- Meta row: gender, birthday, join date --}}
    <div class="flex items-center justify-center gap-2 mt-2 text-[10px] text-gray-400">
        @if($record->gender)
        <span>{{ $record->gender === 'male' ? 'M' : ($record->gender === 'female' ? 'F' : 'O') }}</span>
        <span class="text-gray-300 dark:text-gray-600">&middot;</span>
        @endif
        @if($record->birthday)
        <span>{{ $record->birthday->format('d M') }}</span>
        <span class="text-gray-300 dark:text-gray-600">&middot;</span>
        @endif
        <span>{{ __('Since') }} {{ $record->created_at->format('M Y') }}</span>
    </div>
</div>
