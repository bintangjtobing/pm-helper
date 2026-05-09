<x-filament::widget>
    <x-filament::card>
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Idle Users') }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Users not active in the last 7 days') }}</p>
            </div>
            @if($totalIdle > 0)
                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400">
                    {{ $totalIdle }}
                </span>
            @endif
        </div>

        @if($idleUsers->isEmpty())
            <div class="py-6 text-center">
                <div class="text-3xl">{{ '✓' }}</div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Everyone is active') }}</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($idleUsers as $u)
                    @php
                        $rawAvatar = $u->getAttributes()['avatar_url'] ?? null;
                        $avatarSrc = $rawAvatar ?: ('https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&size=48&background=' . substr(md5($u->id), 0, 6) . '&color=ffffff');

                        if (!$u->last_seen_at) {
                            $label = __('Never logged in');
                            $tone = 'bg-gray-500/10 text-gray-600 dark:text-gray-400';
                        } else {
                            $days = (int) $u->last_seen_at->diffInDays(now());
                            $label = __(':n days idle', ['n' => $days]);
                            $tone = $days > 30
                                ? 'bg-red-500/10 text-red-600 dark:text-red-400'
                                : 'bg-orange-500/10 text-orange-600 dark:text-orange-400';
                        }
                    @endphp
                    <a href="{{ route('filament.resources.users.view', ['record' => $u->id]) }}"
                       class="flex items-center gap-3 p-2 -mx-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <img src="{{ $avatarSrc }}" alt="{{ $u->name }}" class="object-cover w-8 h-8 rounded-full" loading="lazy" />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate dark:text-white">{{ $u->name }}</div>
                            @if($u->department)
                                <div class="text-xs text-gray-500 truncate dark:text-gray-400">{{ $u->department->name }}</div>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $tone }}">{{ $label }}</span>
                    </a>
                @endforeach
            </div>

            @if($totalIdle > $idleUsers->count())
                <div class="mt-3 text-center">
                    <a href="{{ url('/admin/team-activity?filterPresence=idle') }}"
                       class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                        {{ __('View all :n idle users', ['n' => $totalIdle]) }}
                    </a>
                </div>
            @endif
        @endif
    </x-filament::card>
</x-filament::widget>
