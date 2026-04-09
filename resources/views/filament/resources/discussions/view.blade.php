<x-filament::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Discussion Header --}}
            <x-filament::card>
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $record->title }}
                        </h2>
                        <div class="flex items-center gap-2 shrink-0">
                            {!! $record->priority_badge !!}
                            {!! $record->status_badge !!}
                        </div>
                    </div>

                    {{-- Author info --}}
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-700">
                        @php
                            $authorAvatar = $record->user->getAttributes()['avatar_url']
                                ?? ('https://ui-avatars.com/api/?name=' . urlencode($record->user->name) . '&size=64&background=' . substr(md5($record->user->id), 0, 6) . '&color=ffffff');
                        @endphp
                        <img src="{{ $authorAvatar }}" class="object-cover w-8 h-8 rounded-full" />
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->user->name }}</span>
                            <span class="text-sm text-gray-500"> opened this discussion {{ $record->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! \App\Helpers\MentionHelper::renderMentions(\App\Helpers\CodeBlockHelper::autoLinkUrls(Str::markdown(\App\Helpers\CodeBlockHelper::autoDetectCodeBlocks($record->content)))) !!}
                    </div>

                    {{-- Linked ticket / project --}}
                    @if($record->ticket || $record->project)
                    <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        @if($record->project)
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-primary-500/10 text-primary-500">
                            {{ $record->project->name }}
                        </span>
                        @endif
                        @if($record->ticket)
                        <a href="{{ route('filament.resources.tickets.share', $record->ticket->code) }}"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                            &#128279; {{ $record->ticket->code }} — {{ $record->ticket->name }}
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </x-filament::card>

            {{-- Replies Thread --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase">
                    {{ __('Replies') }} ({{ $record->replies->count() }})
                </h3>

                @forelse($record->replies()->with('user')->oldest()->get() as $reply)
                <x-filament::card>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @php
                                    $replyAvatar = $reply->user->getAttributes()['avatar_url']
                                        ?? ('https://ui-avatars.com/api/?name=' . urlencode($reply->user->name) . '&size=64&background=' . substr(md5($reply->user->id), 0, 6) . '&color=ffffff');
                                @endphp
                                <img src="{{ $replyAvatar }}" class="object-cover w-7 h-7 rounded-full" />
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            {!! \App\Helpers\MentionHelper::renderMentions(\App\Helpers\CodeBlockHelper::autoLinkUrls(Str::markdown(\App\Helpers\CodeBlockHelper::autoDetectCodeBlocks($reply->content)))) !!}
                        </div>
                    </div>
                </x-filament::card>
                @empty
                <x-filament::card>
                    <div class="py-6 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">{{ __('No replies yet. Be the first to respond!') }}</p>
                    </div>
                </x-filament::card>
                @endforelse

                {{-- Reply Form --}}
                @if(!in_array($record->status, ['closed']))
                <x-filament::card>
                    <form wire:submit.prevent="submitReply">
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Write a reply') }} <span class="text-xs text-gray-400">— {{ __('Type @ to mention someone') }}</span>
                            </label>
                            <div data-enable-mentions="true" data-users="{{ $this->getMentionUsersJson() }}">
                                <textarea
                                    id="discussion-reply-textarea"
                                    wire:model.defer="replyContent"
                                    rows="4"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    placeholder="{{ __('Type your reply here... Use @ to mention someone') }}"
                                ></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-600 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    {{ __('Reply') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </x-filament::card>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <x-filament::card>
                <dl class="space-y-4">
                    {{-- Status --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</dt>
                        <dd class="mt-1">{!! $record->status_badge !!}</dd>
                    </div>

                    {{-- Priority --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Priority') }}</dt>
                        <dd class="mt-1">{!! $record->priority_badge !!}</dd>
                    </div>

                    {{-- Author --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Author') }}</dt>
                        <dd class="flex items-center gap-2 mt-1">
                            <img src="{{ $authorAvatar }}" class="object-cover w-6 h-6 rounded-full" />
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->user->name }}</span>
                        </dd>
                    </div>

                    {{-- Project --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Project') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $record->project?->name ?? __('General') }}
                        </dd>
                    </div>

                    {{-- Linked Ticket --}}
                    @if($record->ticket)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Linked Ticket') }}</dt>
                        <dd class="mt-1">
                            <a href="{{ route('filament.resources.tickets.share', $record->ticket->code) }}"
                               target="_blank"
                               class="text-sm text-blue-600 hover:underline">
                                {{ $record->ticket->code }}
                            </a>
                        </dd>
                    </div>
                    @endif

                    {{-- Replies --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Replies') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->replies->count() }}</dd>
                    </div>

                    {{-- Resolved --}}
                    @if($record->resolved_at)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Resolved By') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $record->resolvedByUser?->name }}
                            <span class="text-xs text-gray-500">({{ $record->resolved_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                    @endif

                    {{-- Created --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Created') }}</dt>
                        <dd class="mt-1 text-sm text-gray-500">
                            {{ $record->created_at->format('d M Y H:i') }}
                            <span class="text-xs">({{ $record->created_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                </dl>
            </x-filament::card>
        </div>
    </div>

    <script>
        window.mentionUsers = {!! $this->getMentionUsersJson() !!};
    </script>
    <script src="{{ asset('js/mentions.js') }}"></script>
</x-filament::page>
