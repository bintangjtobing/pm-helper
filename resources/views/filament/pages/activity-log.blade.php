<x-filament::page>
    {{-- Stats Cards --}}
    @php $stats = $this->stats; @endphp
    <div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-4">
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_changes']) }}</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Status Changes') }}</div>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['unique_users'] }}</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Active Members') }}</div>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['unique_tickets'] }}</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Tickets Touched') }}</div>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="text-sm font-semibold text-gray-900 truncate dark:text-white">{{ $stats['top_transition'] ?? '-' }}</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Top Transition') }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="p-4 mb-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
        <div class="grid items-end grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('User') }}</label>
                <select wire:model="filterUser" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">{{ __('All Users') }}</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Project') }}</label>
                <select wire:model="filterProject" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">{{ __('All Projects') }}</option>
                    @foreach($this->projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('To Status') }}</label>
                <select wire:model="filterStatus" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach($this->statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Period') }}</label>
                <select wire:model="filterDateRange" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                    <option value="1">{{ __('Today') }}</option>
                    <option value="7">{{ __('Last 7 days') }}</option>
                    <option value="14">{{ __('Last 14 days') }}</option>
                    <option value="30">{{ __('Last 30 days') }}</option>
                    <option value="90">{{ __('Last 90 days') }}</option>
                    <option value="custom">{{ __('Custom range') }}</option>
                    <option value="all">{{ __('All time') }}</option>
                </select>
            </div>
            @if($filterDateRange === 'custom')
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('From') }}</label>
                <input type="date" wire:model="filterDateFrom" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('To') }}</label>
                <input type="date" wire:model="filterDateTo" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
            </div>
            @endif
            <div class="flex items-end gap-2">
                <button wire:click="applyFilters" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-600 hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('Filter') }}
                </button>
                <button wire:click="resetFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('Reset') }}
                </button>
            </div>
        </div>
    </div>

    {{-- View Mode Toggle --}}
    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Showing :count activities', ['count' => $this->activities->count()]) }}
        </div>
        <div class="inline-flex overflow-hidden border border-gray-200 rounded-lg dark:border-gray-600">
            <button wire:click="setViewMode('timeline')"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium transition-colors {{ $viewMode === 'timeline' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                {{ __('Timeline') }}
            </button>
            <button wire:click="setViewMode('tree')"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium transition-colors border-l border-gray-200 dark:border-gray-600 {{ $viewMode === 'tree' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                {{ __('Tree View') }}
            </button>
        </div>
    </div>

    @if($viewMode === 'timeline')
    {{-- ═══════════════════════════════════════════════════════════════════
         TIMELINE VIEW
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
        @php
            $activities = $this->activities;
            $groupedByDate = $activities->groupBy(fn ($a) => $a->created_at->format('Y-m-d'));
        @endphp

        @forelse($groupedByDate as $date => $dayActivities)
            <div class="sticky top-0 z-10 px-5 py-2.5 text-xs font-semibold tracking-wide text-gray-500 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                @php $dateObj = \Carbon\Carbon::parse($date); @endphp
                @if($dateObj->isToday())
                    {{ __('Today') }} &mdash; {{ $dateObj->format('D, M d') }}
                @elseif($dateObj->isYesterday())
                    {{ __('Yesterday') }} &mdash; {{ $dateObj->format('D, M d') }}
                @else
                    {{ $dateObj->format('l, M d, Y') }}
                @endif
                <span class="ml-2 text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $dayActivities->count() }}</span>
            </div>

            @foreach($dayActivities as $activity)
                @php
                    $user = $activity->user;
                    $ticket = $activity->ticket;
                    $oldStatus = $activity->oldStatus;
                    $newStatus = $activity->newStatus;
                    if (!$user || !$ticket) continue;
                    $avatarUrl = $user->getAttributes()['avatar_url']
                        ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                    $ticketUrl = route('filament.resources.tickets.share', $ticket->code);
                @endphp
                <div class="flex items-start gap-4 px-5 py-3 transition-colors border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="object-cover rounded-full w-9 h-9 shrink-0 ring-2 ring-blue-500/20" loading="lazy">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('changed status on') }}</span>
                            <a href="{{ $ticketUrl }}" target="_blank" class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                <span class="font-mono text-xs">{{ $ticket->code }}</span>
                                <span class="truncate max-w-[200px]">{{ $ticket->name }}</span>
                            </a>
                            <span class="ml-auto text-xs text-gray-400 dark:text-gray-500 shrink-0">{{ $activity->created_at->format('H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-3 mt-1.5">
                            @if($ticket->project)
                            <span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500">{{ $ticket->project->name }}</span>
                            @endif
                            @if($oldStatus && $newStatus)
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md" style="background-color: {{ $oldStatus->color ?? '#6B7280' }}20; color: {{ $oldStatus->color ?? '#6B7280' }}">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $oldStatus->color ?? '#6B7280' }}"></span>
                                    {{ $oldStatus->name }}
                                </span>
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md" style="background-color: {{ $newStatus->color ?? '#6B7280' }}20; color: {{ $newStatus->color ?? '#6B7280' }}">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $newStatus->color ?? '#6B7280' }}"></span>
                                    {{ $newStatus->name }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @empty
            <div class="py-16 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('No activities found for the selected filters.') }}</p>
            </div>
        @endforelse

        @if($activities->count() >= $perPage * $currentPage)
        <div class="py-3 text-center border-t border-gray-200 dark:border-gray-700">
            <button wire:click="loadMore" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                {{ __('Load more activities') }}
            </button>
        </div>
        @endif
    </div>

    @else
    {{-- ═══════════════════════════════════════════════════════════════════
         TREE VIEW — Project → Ticket → Status changes
         ═══════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-3">
        @forelse($this->treeData as $projectNode)
            @php $project = $projectNode['project']; @endphp
            <div x-data="{ open: true }" class="overflow-hidden bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                {{-- Project header --}}
                <button type="button" x-on:click="open = !open"
                    class="flex items-center justify-between w-full gap-3 px-5 py-3 text-left transition-colors bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-4 h-4 transition-transform text-primary-500 shrink-0" x-bind:class="open && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <svg class="w-5 h-5 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span class="text-sm font-bold text-gray-900 truncate dark:text-white">{{ $project->name }}</span>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-primary-500/10 text-primary-500">{{ $projectNode['tickets']->count() }} {{ __('tickets') }}</span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-500">{{ $projectNode['total_changes'] }} {{ __('changes') }}</span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-500/10 text-green-500">{{ $projectNode['unique_users'] }} {{ __('members') }}</span>
                    </div>
                </button>

                {{-- Tickets --}}
                <div x-show="open" x-collapse>
                    @foreach($projectNode['tickets'] as $ticketNode)
                        @php
                            $ticket = $ticketNode['ticket'];
                            $ticketUrl = route('filament.resources.tickets.share', $ticket->code);
                        @endphp
                        <div x-data="{ expanded: false }" class="border-t border-gray-100 dark:border-gray-700/50">
                            {{-- Ticket header --}}
                            <button type="button" x-on:click="expanded = !expanded"
                                class="flex items-center justify-between w-full gap-3 px-5 py-2.5 pl-12 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <svg class="w-3.5 h-3.5 transition-transform text-gray-400 shrink-0" x-bind:class="expanded && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    <span class="font-mono text-xs font-semibold text-primary-600 dark:text-primary-400">{{ $ticket->code }}</span>
                                    <span class="text-sm text-gray-700 truncate dark:text-gray-200">{{ $ticket->name }}</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    {{-- Member avatars --}}
                                    <div class="flex -space-x-1.5">
                                        @foreach($ticketNode['users']->take(4) as $member)
                                            @php
                                                $memberAvatar = $member->getAttributes()['avatar_url']
                                                    ?? ('https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&size=32&background=' . substr(md5($member->id), 0, 6) . '&color=ffffff');
                                            @endphp
                                            <img src="{{ $memberAvatar }}" alt="{{ $member->name }}" title="{{ $member->name }}" class="w-5 h-5 rounded-full ring-1 ring-white dark:ring-gray-800" loading="lazy">
                                        @endforeach
                                        @if($ticketNode['users']->count() > 4)
                                            <span class="flex items-center justify-center w-5 h-5 text-[9px] font-semibold text-gray-500 bg-gray-200 rounded-full dark:bg-gray-600 dark:text-gray-300 ring-1 ring-white dark:ring-gray-800">+{{ $ticketNode['users']->count() - 4 }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400">{{ $ticketNode['activities']->count() }}</span>
                                    <a href="{{ $ticketUrl }}" target="_blank" x-on:click.stop class="p-1 text-gray-400 rounded hover:text-primary-500" title="{{ __('Open ticket') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </button>

                            {{-- Activity entries --}}
                            <div x-show="expanded" x-collapse>
                                @foreach($ticketNode['activities'] as $activity)
                                    @php
                                        $user = $activity->user;
                                        $oldStatus = $activity->oldStatus;
                                        $newStatus = $activity->newStatus;
                                        if (!$user) continue;
                                        $avatarUrl = $user->getAttributes()['avatar_url']
                                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
                                    @endphp
                                    <div class="flex items-center gap-3 py-2 pl-20 pr-5 border-t border-gray-50 dark:border-gray-700/30">
                                        {{-- Vertical line connector --}}
                                        <div class="relative flex items-center shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                        </div>
                                        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="object-cover w-6 h-6 rounded-full shrink-0" loading="lazy">
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 shrink-0">{{ $user->name }}</span>
                                        @if($oldStatus && $newStatus)
                                        <div class="flex items-center gap-1">
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-medium rounded" style="background-color: {{ $oldStatus->color ?? '#6B7280' }}15; color: {{ $oldStatus->color ?? '#6B7280' }}">
                                                <span class="w-1 h-1 rounded-full" style="background-color: {{ $oldStatus->color ?? '#6B7280' }}"></span>
                                                {{ $oldStatus->name }}
                                            </span>
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-medium rounded" style="background-color: {{ $newStatus->color ?? '#6B7280' }}15; color: {{ $newStatus->color ?? '#6B7280' }}">
                                                <span class="w-1 h-1 rounded-full" style="background-color: {{ $newStatus->color ?? '#6B7280' }}"></span>
                                                {{ $newStatus->name }}
                                            </span>
                                        </div>
                                        @endif
                                        <span class="ml-auto text-[10px] text-gray-400 dark:text-gray-500 shrink-0">{{ $activity->created_at->format('M d, H:i') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="py-16 text-center bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('No activities found for the selected filters.') }}</p>
            </div>
        @endforelse
    </div>
    @endif
</x-filament::page>
