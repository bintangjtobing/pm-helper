@php
    $vd = $this->getViewData();
    $users = $vd['users'];
    $stats = $vd['stats'];
    $ticketCounts = $vd['ticketCounts'];
    $departments = $vd['departments'];
    $roles = $vd['roles'];

    $toneStyles = [
        'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 ring-emerald-500/20',
        'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 ring-amber-500/20',
        'orange' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 ring-orange-500/20',
        'red' => 'bg-red-500/10 text-red-600 dark:text-red-400 ring-red-500/20',
        'gray' => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 ring-gray-500/20',
    ];
    $toneDot = [
        'emerald' => 'bg-emerald-500',
        'amber' => 'bg-amber-500',
        'orange' => 'bg-orange-500',
        'red' => 'bg-red-500',
        'gray' => 'bg-gray-400',
    ];
@endphp

<x-filament::page>
    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Total Users') }}</div>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-baseline gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['active'] }}</span>
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Active (24h)') }}</div>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-baseline gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-500"></span>
                <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['this_week'] }}</span>
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('This Week') }}</div>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-baseline gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                <span class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['idle'] }}</span>
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Idle (>7d)') }}</div>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-baseline gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-gray-400"></span>
                <span class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $stats['never'] }}</span>
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Never Logged In') }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="p-4 mt-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Search') }}</label>
                <input type="text" wire:model.debounce.500ms="filterSearch" placeholder="{{ __('Name, email, username') }}"
                    class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Presence') }}</label>
                <select wire:model="filterPresence"
                    class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="all">{{ __('All') }}</option>
                    <option value="active">{{ __('Active (today / last 1h)') }}</option>
                    <option value="recent">{{ __('Recent (1-7 days)') }}</option>
                    <option value="idle">{{ __('Idle (8-30 days)') }}</option>
                    <option value="dormant">{{ __('Dormant (>30 days)') }}</option>
                    <option value="never">{{ __('Never logged in') }}</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Department') }}</label>
                <select wire:model="filterDepartment"
                    class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">{{ __('All departments') }}</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('Role') }}</label>
                <select wire:model="filterRole"
                    class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">{{ __('All roles') }}</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" wire:click="resetFilters"
                    class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Reset') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Users list --}}
    <div class="mt-4 overflow-hidden bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('User') }}</th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Department') }}</th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Role') }}</th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Last Seen') }}</th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Open Tickets') }}</th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Joined') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($users as $u)
                        @php
                            $rawAvatar = $u->getAttributes()['avatar_url'] ?? null;
                            $avatarSrc = $rawAvatar ?: ('https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&size=64&background=' . substr(md5($u->id), 0, 6) . '&color=ffffff');
                            $tone = $u->presence_tone;
                            $openCount = $ticketCounts[$u->id] ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $avatarSrc }}" alt="{{ $u->name }}" class="object-cover w-8 h-8 rounded-full" loading="lazy" />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $u->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($u->department)
                                    <div class="flex items-center gap-1.5">
                                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $u->department->color }};display:inline-block;"></span>
                                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $u->department->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('No department') }}</span>
                                @endif
                                @if($u->position)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $u->position->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($u->roles as $role)
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 {{ $toneStyles[$tone] ?? $toneStyles['gray'] }}">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full {{ $toneDot[$tone] ?? $toneDot['gray'] }}"></span>
                                    {{ $u->presence_label }}
                                </div>
                                @if($u->last_seen_at)
                                    <div class="mt-1 text-[10px] text-gray-400">{{ $u->last_seen_at->format('Y-m-d H:i') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($openCount > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                        {{ $openCount }} {{ __('open') }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('None') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap dark:text-gray-400">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-sm text-center text-gray-500 dark:text-gray-400">
                                {{ __('No users match the current filters.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
