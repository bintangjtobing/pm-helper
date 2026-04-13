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
            {{-- User --}}
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('User') }}</label>
                <select wire:model="filterUser" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">{{ __('All Users') }}</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Project --}}
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Project') }}</label>
                <select wire:model="filterProject" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">{{ __('All Projects') }}</option>
                    @foreach($this->projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Target Status --}}
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('To Status') }}</label>
                <select wire:model="filterStatus" class="w-full text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach($this->statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Range --}}
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

            {{-- Custom date inputs --}}
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

            {{-- Action Buttons --}}
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

    {{-- Activity Timeline --}}
    <div class="overflow-hidden bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
        @php
            $activities = $this->activities;
            $groupedByDate = $activities->groupBy(fn ($a) => $a->created_at->format('Y-m-d'));
        @endphp

        @forelse($groupedByDate as $date => $dayActivities)
            {{-- Date header --}}
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
                    {{-- Avatar --}}
                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="object-cover rounded-full w-9 h-9 shrink-0 ring-2 ring-blue-500/20" loading="lazy">

                    {{-- Content --}}
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
                            {{-- Project badge --}}
                            @if($ticket->project)
                            <span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500">{{ $ticket->project->name }}</span>
                            @endif

                            {{-- Status transition --}}
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

        {{-- Load more --}}
        @if($activities->count() >= $perPage * $currentPage)
        <div class="py-3 text-center border-t border-gray-200 dark:border-gray-700">
            <button wire:click="loadMore" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">
                {{ __('Load more activities') }}
            </button>
        </div>
        @endif
    </div>
</x-filament::page>
