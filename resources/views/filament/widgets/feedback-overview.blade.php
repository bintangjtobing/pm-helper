<x-filament::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Customer Feedback') }}
                </h3>
                <a href="{{ route('filament.resources.customer-feedbacks.index') }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
                    {{ __('View all') }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                    {{ $pendingCount }} {{ __('Pending') }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    {{ $convertedCount }} {{ __('Converted') }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    {{ $rejectedCount }} {{ __('Rejected') }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('This week') }}</div>
                    <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $weekSubmitted }}</div>
                    <div class="text-[11px] text-gray-400">{{ __('new feedback') }}</div>
                </div>
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Conversion rate') }}</div>
                    <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">
                        @if($conversionRate === null)
                            <span class="text-gray-400">—</span>
                        @else
                            {{ $conversionRate }}<span class="text-sm text-gray-400">%</span>
                        @endif
                    </div>
                    <div class="text-[11px] text-gray-400">{{ $weekConverted }} / {{ $weekSubmitted }} {{ __('this week') }}</div>
                </div>
            </div>

            <div class="space-y-2">
                @forelse($latestPending as $feedback)
                    <a href="{{ route('filament.resources.customer-feedbacks.view', $feedback->id) }}"
                       class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                        @php
                            $name = $feedback->user?->name ?? 'Anonymous';
                            $avatar = $feedback->user?->getAttributes()['avatar_url']
                                ?? ('https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=64&background=' . substr(md5($feedback->user_id ?? 'a'), 0, 6) . '&color=ffffff');
                        @endphp
                        <img src="{{ $avatar }}" class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5" loading="lazy" />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-primary-600">
                                {{ $feedback->title }}
                            </div>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs text-gray-500">{{ $name }}</span>
                                @if($feedback->project)
                                    <span class="text-xs text-gray-300 dark:text-gray-600">&middot;</span>
                                    <span class="text-xs text-gray-400 truncate">{{ $feedback->project->name }}</span>
                                @endif
                                <span class="text-xs text-gray-300 dark:text-gray-600">&middot;</span>
                                <span class="text-xs text-gray-400">{{ $feedback->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-6 text-sm text-gray-400">
                        {{ __('No pending feedback. Inbox zero. 🎉') }}
                    </div>
                @endforelse
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
