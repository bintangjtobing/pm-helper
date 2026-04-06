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

        {{-- Tab switcher --}}
        <div x-data="{ tab: 'chart' }" class="space-y-4">
            <div class="flex gap-2">
                <button @click="tab = 'chart'" :class="tab === 'chart' ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                    {{ __('Organization Chart') }}
                </button>
                <button @click="tab = 'data'" :class="tab === 'data' ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                    {{ __('Department Data') }}
                </button>
            </div>

            {{-- CHART TAB --}}
            <div x-show="tab === 'chart'" class="overflow-x-auto">
                <div class="min-w-[800px] py-8">
                    @php
                        // Build hierarchy: users with no supervisor = top level
                        $allUsers = \App\Models\User::with(['position', 'department', 'subordinates.position', 'subordinates.department', 'subordinates.subordinates.position', 'subordinates.subordinates.department'])
                            ->whereNotNull('position_id')
                            ->get();
                        $topUsers = $allUsers->whereNull('supervisor_id');
                        if ($topUsers->isEmpty()) {
                            // Fallback: show highest level positions
                            $topUsers = $allUsers->filter(fn($u) => $u->position && $u->position->level >= 3);
                        }
                    @endphp

                    @if($topUsers->isNotEmpty())
                    <div class="flex flex-col items-center gap-8">
                        {{-- Top level --}}
                        <div class="flex flex-wrap justify-center gap-8">
                            @foreach($topUsers as $user)
                                @include('filament.pages.partials.org-node', ['user' => $user, 'depth' => 0])
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="py-16 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="mt-4 text-sm text-gray-500">{{ __('No organization structure yet.') }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ __('Assign departments, positions, and supervisors in user profiles.') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- DATA TAB --}}
            <div x-show="tab === 'data'" class="space-y-6">
                {{-- Core --}}
                <div>
                    <h3 class="mb-4 text-sm font-semibold text-gray-500 uppercase">{{ __('Core Departments') }}</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($departments->where('category', 'core') as $dept)
                        <x-filament::card>
                            <div class="space-y-3">
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
                                @php $grouped = $dept->positions->groupBy('sub_division'); @endphp
                                <div class="space-y-1">
                                    @foreach($grouped as $subDiv => $positions)
                                        @if($subDiv)
                                        <div class="pt-1"><span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $subDiv }}</span></div>
                                        @endif
                                        @foreach($positions as $pos)
                                        <div class="flex items-center justify-between py-0.5">
                                            <div class="flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $pos->level >= 4 ? 'bg-red-500' : ($pos->level >= 3 ? 'bg-orange-500' : ($pos->level >= 2 ? 'bg-blue-500' : ($pos->level >= 1 ? 'bg-green-500' : 'bg-gray-400'))) }}"></span>
                                                <span class="text-xs text-gray-700 dark:text-gray-300">{{ $pos->name }}</span>
                                            </div>
                                            @if($pos->users->count() > 0)
                                            <div class="flex items-center -space-x-1">
                                                @foreach($pos->users->take(3) as $user)
                                                @php $av = $user->getAttributes()['avatar_url'] ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff'); @endphp
                                                <img src="{{ $av }}" title="{{ $user->name }}" class="w-5 h-5 rounded-full object-cover border border-white dark:border-gray-800" />
                                                @endforeach
                                            </div>
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

                {{-- Advanced --}}
                @if($departments->where('category', 'advanced')->count() > 0)
                <div>
                    <h3 class="mb-4 text-sm font-semibold text-gray-500 uppercase">{{ __('Advanced Departments') }}</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($departments->where('category', 'advanced') as $dept)
                        <x-filament::card>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div style="width:12px;height:12px;border-radius:50%;background:{{ $dept->color }};flex-shrink:0;"></div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $dept->name }}</h4>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-orange-500/10 text-orange-500">Advanced</span>
                                </div>
                                <div class="space-y-1">
                                    @foreach($dept->positions as $pos)
                                    <div class="flex items-center gap-2 py-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                                        <span class="text-xs text-gray-700 dark:text-gray-300">{{ $pos->name }}</span>
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
        </div>
    </div>
</x-filament::page>
