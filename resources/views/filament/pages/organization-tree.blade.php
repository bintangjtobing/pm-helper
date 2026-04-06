<x-filament::page>
    <div class="space-y-6">
        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $departments->count() }}</div>
                    <div class="text-xs text-gray-500">{{ __('Departments') }}</div>
                </div>
            </x-filament::card>
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $departments->sum(fn($d) => $d->positions->count()) }}</div>
                    <div class="text-xs text-gray-500">{{ __('Positions') }}</div>
                </div>
            </x-filament::card>
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalUsers }}</div>
                    <div class="text-xs text-gray-500">{{ __('Team Members') }}</div>
                </div>
            </x-filament::card>
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $departments->where('category', 'core')->count() }}</div>
                    <div class="text-xs text-gray-500">{{ __('Core Departments') }}</div>
                </div>
            </x-filament::card>
        </div>

        {{-- Core Departments --}}
        <div>
            <h3 class="mb-4 text-sm font-semibold text-gray-500 uppercase">{{ __('Core Departments') }}</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($departments->where('category', 'core') as $dept)
                <x-filament::card>
                    <div class="space-y-3">
                        {{-- Department Header --}}
                        <div class="flex items-center gap-3">
                            <div style="width:12px;height:12px;border-radius:50%;background:{{ $dept->color }};flex-shrink:0;"></div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $dept->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $dept->description }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full" style="background:{{ $dept->color }}20;color:{{ $dept->color }};">
                                {{ $dept->users->count() }} {{ __('members') }}
                            </span>
                        </div>

                        {{-- Positions --}}
                        @php
                            $grouped = $dept->positions->groupBy('sub_division');
                        @endphp

                        <div class="space-y-2">
                            @foreach($grouped as $subDiv => $positions)
                                @if($subDiv)
                                <div class="pt-1">
                                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $subDiv }}</span>
                                </div>
                                @endif

                                @foreach($positions as $pos)
                                <div class="flex items-center justify-between py-1 pl-{{ $subDiv ? '3' : '0' }}">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                            {{ $pos->level >= 4 ? 'bg-red-500' : ($pos->level >= 3 ? 'bg-orange-500' : ($pos->level >= 2 ? 'bg-blue-500' : ($pos->level >= 1 ? 'bg-green-500' : 'bg-gray-400'))) }}
                                        "></span>
                                        <span class="text-xs text-gray-700 dark:text-gray-300">{{ $pos->name }}</span>
                                    </div>
                                    @if($pos->users->count() > 0)
                                    <div class="flex items-center -space-x-1">
                                        @foreach($pos->users->take(3) as $user)
                                        @php
                                            $avatar = $user->getAttributes()['avatar_url']
                                                ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                                        @endphp
                                        <img src="{{ $avatar }}" title="{{ $user->name }}" class="w-5 h-5 rounded-full object-cover border border-white dark:border-gray-800" />
                                        @endforeach
                                        @if($pos->users->count() > 3)
                                        <span class="flex items-center justify-center w-5 h-5 text-[9px] font-medium text-gray-500 bg-gray-200 dark:bg-gray-700 rounded-full border border-white dark:border-gray-800">
                                            +{{ $pos->users->count() - 3 }}
                                        </span>
                                        @endif
                                    </div>
                                    @else
                                    <span class="text-[10px] text-gray-400">—</span>
                                    @endif
                                </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </x-filament::card>
                @endforeach
            </div>
        </div>

        {{-- Advanced Departments --}}
        @if($departments->where('category', 'advanced')->count() > 0)
        <div>
            <h3 class="mb-4 text-sm font-semibold text-gray-500 uppercase">{{ __('Advanced Departments') }}</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($departments->where('category', 'advanced') as $dept)
                <x-filament::card>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div style="width:12px;height:12px;border-radius:50%;background:{{ $dept->color }};flex-shrink:0;"></div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $dept->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $dept->description }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-orange-500/10 text-orange-500">
                                {{ __('Advanced') }}
                            </span>
                        </div>

                        <div class="space-y-2">
                            @foreach($dept->positions as $pos)
                            <div class="flex items-center justify-between py-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $pos->name }}</span>
                                </div>
                                @if($pos->users->count() > 0)
                                <div class="flex items-center -space-x-1">
                                    @foreach($pos->users->take(3) as $user)
                                    @php
                                        $avatar = $user->getAttributes()['avatar_url']
                                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                                    @endphp
                                    <img src="{{ $avatar }}" title="{{ $user->name }}" class="w-5 h-5 rounded-full object-cover border border-white dark:border-gray-800" />
                                    @endforeach
                                </div>
                                @else
                                <span class="text-[10px] text-gray-400">—</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </x-filament::card>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-filament::page>
