<x-filament::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Discussions') }}
                </h3>
                <a href="{{ route('filament.resources.discussions.create') }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-white rounded-lg bg-primary-600 hover:bg-primary-500">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New') }}
                </a>
            </div>

            {{-- Status Counters --}}
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    {{ $openCount }} {{ __('Open') }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                    {{ $inDiscussionCount }} {{ __('In Discussion') }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    {{ $resolvedCount }} {{ __('Resolved') }}
                </span>
                @if($highPriorityOpen > 0)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $highPriorityOpen }} {{ __('High Priority') }}
                </span>
                @endif
            </div>

            {{-- Active Discussions List --}}
            <div class="space-y-2">
                @forelse($latestDiscussions as $discussion)
                <a href="{{ route('filament.resources.discussions.view', $discussion->id) }}"
                   class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                    {{-- Avatar --}}
                    @php
                        $avatar = $discussion->user->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($discussion->user->name) . '&size=64&background=' . substr(md5($discussion->user->id), 0, 6) . '&color=ffffff');
                    @endphp
                    <img src="{{ $avatar }}" class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5" loading="lazy" />

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-primary-600">
                                {{ $discussion->title }}
                            </span>
                            @if($discussion->priority === 'high')
                            <span class="shrink-0 px-1.5 py-0.5 text-[10px] font-semibold rounded bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">HIGH</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-gray-500">{{ $discussion->user->name }}</span>
                            <span class="text-xs text-gray-300 dark:text-gray-600">&middot;</span>
                            <span class="text-xs text-gray-400">{{ $discussion->created_at->diffForHumans() }}</span>
                            @if($discussion->replies_count > 0)
                            <span class="text-xs text-gray-300 dark:text-gray-600">&middot;</span>
                            <span class="inline-flex items-center gap-1 text-xs text-blue-500">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                {{ $discussion->replies_count }}
                            </span>
                            @endif
                        </div>
                        @if($discussion->project)
                        <span class="inline-block mt-1 px-1.5 py-0.5 text-[10px] font-medium rounded bg-primary-500/10 text-primary-500">
                            {{ $discussion->project->name }}
                        </span>
                        @endif
                    </div>

                    {{-- Status dot --}}
                    <span class="shrink-0 mt-1 w-2 h-2 rounded-full {{ $discussion->status === 'open' ? 'bg-blue-500' : 'bg-yellow-500' }}"></span>
                </a>
                @empty
                <div class="py-6 text-center">
                    <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">{{ __('No active discussions') }}</p>
                </div>
                @endforelse
            </div>

            {{-- Footer --}}
            @if($latestDiscussions->count() > 0)
            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('filament.resources.discussions.index') }}" class="text-xs font-medium text-blue-600 hover:underline">
                    {{ __('View all discussions') }} →
                </a>
            </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>
