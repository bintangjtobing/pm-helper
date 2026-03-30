@php
    $record = $this->record;
    use Illuminate\Support\Str;
@endphp
<x-filament::page>

    <div class="flex flex-col w-full gap-5 md:flex-row">

        {{-- Left Column: Main Content --}}
        <x-filament::card class="flex flex-col w-full gap-5 md:w-2/3">

            {{-- Header --}}
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ __('Weekly Report') }}
                    </h2>
                    {!! $record->status_badge !!}
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <x-user-avatar :user="$record->user" />
                    <span class="font-medium">{{ $record->user->name }}</span>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span>{{ $record->week_label }}</span>
                    @if($record->project)
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <span>{{ $record->project->name }}</span>
                    @endif
                </div>
            </div>

            {{-- Auto-Generated Summary --}}
            @if($record->auto_summary)
            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/10 dark:border-blue-800">
                <h3 class="flex items-center gap-2 mb-3 text-sm font-semibold text-blue-700 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('Auto-Generated Summary') }}
                </h3>

                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    @php
                        $summary = $record->auto_summary;
                        $ticketsUpdated = count($summary['tickets_updated'] ?? []);
                        $ticketsCompleted = count($summary['tickets_completed'] ?? []);
                        $statusChanges = count($summary['status_changes'] ?? []);
                        $totalHours = $summary['hours_logged']['total_hours'] ?? 0;
                    @endphp

                    <div class="p-3 text-center bg-white rounded-lg dark:bg-gray-800">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $ticketsUpdated }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Tickets Updated') }}</div>
                    </div>
                    <div class="p-3 text-center bg-white rounded-lg dark:bg-gray-800">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $ticketsCompleted }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Completed') }}</div>
                    </div>
                    <div class="p-3 text-center bg-white rounded-lg dark:bg-gray-800">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $statusChanges }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Status Changes') }}</div>
                    </div>
                    <div class="p-3 text-center bg-white rounded-lg dark:bg-gray-800">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $totalHours }}h</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Hours Logged') }}</div>
                    </div>
                </div>

                {{-- Detailed lists --}}
                @if(!empty($summary['tickets_completed']))
                <div class="mt-4">
                    <h4 class="mb-2 text-xs font-semibold text-blue-600 uppercase dark:text-blue-400">{{ __('Tickets Completed') }}</h4>
                    <div class="space-y-1">
                        @foreach(array_slice($summary['tickets_completed'], 0, 10) as $ticket)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="px-1.5 py-0.5 text-xs font-mono bg-green-100 text-green-700 rounded dark:bg-green-900/30 dark:text-green-400">{{ $ticket['code'] }}</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ $ticket['name'] }}</span>
                            <span class="text-xs text-gray-400">({{ $ticket['project_name'] }})</span>
                        </div>
                        @endforeach
                        @if(count($summary['tickets_completed']) > 10)
                        <div class="text-xs text-gray-400">...and {{ count($summary['tickets_completed']) - 10 }} more</div>
                        @endif
                    </div>
                </div>
                @endif

                @if(!empty($summary['tickets_updated']))
                <div class="mt-4">
                    <h4 class="mb-2 text-xs font-semibold text-blue-600 uppercase dark:text-blue-400">{{ __('Tickets Updated') }}</h4>
                    <div class="space-y-1">
                        @foreach(array_slice($summary['tickets_updated'], 0, 10) as $ticket)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="px-1.5 py-0.5 text-xs font-mono bg-blue-100 text-blue-700 rounded dark:bg-blue-900/30 dark:text-blue-400">{{ $ticket['code'] }}</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ $ticket['name'] }}</span>
                            <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded dark:bg-gray-700 dark:text-gray-400">{{ $ticket['status'] }}</span>
                        </div>
                        @endforeach
                        @if(count($summary['tickets_updated']) > 10)
                        <div class="text-xs text-gray-400">...and {{ count($summary['tickets_updated']) - 10 }} more</div>
                        @endif
                    </div>
                </div>
                @endif

                @if(!empty($summary['status_changes']))
                <div class="mt-4">
                    <h4 class="mb-2 text-xs font-semibold text-blue-600 uppercase dark:text-blue-400">{{ __('Status Changes') }}</h4>
                    <div class="space-y-1">
                        @foreach(array_slice($summary['status_changes'], 0, 10) as $change)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="px-1.5 py-0.5 text-xs font-mono bg-purple-100 text-purple-700 rounded dark:bg-purple-900/30 dark:text-purple-400">{{ $change['ticket_code'] }}</span>
                            <span class="text-gray-400">{{ $change['from_status'] }}</span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $change['to_status'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($summary['hours_logged']['entries']))
                <div class="mt-4">
                    <h4 class="mb-2 text-xs font-semibold text-blue-600 uppercase dark:text-blue-400">{{ __('Time Logged') }} ({{ $totalHours }}h)</h4>
                    <div class="space-y-1">
                        @foreach(array_slice($summary['hours_logged']['entries'], 0, 10) as $entry)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="px-1.5 py-0.5 text-xs font-mono bg-orange-100 text-orange-700 rounded dark:bg-orange-900/30 dark:text-orange-400">{{ $entry['ticket_code'] }}</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $entry['hours'] }}h</span>
                            @if($entry['activity'])
                            <span class="text-xs text-gray-400">({{ $entry['activity'] }})</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- User-Written Content --}}
            @if($record->content)
            <div class="flex flex-col gap-2">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Report Notes') }}</h3>
                <div class="w-full prose-sm prose max-w-none dark:prose-invert">
                    {!! $record->content !!}
                </div>
            </div>
            @endif

            {{-- Tabs --}}
            <div class="flex items-center w-full gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <button wire:click="selectTab('feedback')"
                    class="text-sm font-medium px-3 py-2 border-b-2 border-transparent hover:border-primary-500 flex items-center gap-1 @if($tab === 'feedback') border-primary-500 text-primary-500 @else text-gray-500 @endif">
                    {{ __('Feedback') }}
                    @if($record->feedbacks->count() > 0)
                    <span class="px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ $record->feedbacks->count() }}
                    </span>
                    @endif
                </button>
                <button wire:click="selectTab('attachments')"
                    class="text-sm font-medium px-3 py-2 border-b-2 border-transparent hover:border-primary-500 flex items-center gap-1 @if($tab === 'attachments') border-primary-500 text-primary-500 @else text-gray-500 @endif">
                    {{ __('Attachments') }}
                    @if($record->getMedia('attachments')->count() > 0)
                    <span class="px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ $record->getMedia('attachments')->count() }}
                    </span>
                    @endif
                </button>
            </div>

            {{-- Feedback Tab --}}
            @if($tab === 'feedback')
            @if($this->canGiveFeedback())
            <form wire:submit.prevent="submitFeedback" class="pb-5">
                {{ $this->form }}
                <button type="submit" class="px-3 py-2 mt-3 text-white rounded bg-primary-500 hover:bg-primary-600">
                    {{ __('Submit Feedback') }}
                </button>
            </form>
            @endif

            @foreach($record->feedbacks->sortByDesc('created_at') as $feedback)
            <div class="w-full flex flex-col gap-2 @if(!$loop->last) pb-5 mb-5 border-b border-gray-200 dark:border-gray-700 @endif">
                <div class="flex justify-between w-full">
                    <span class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1 font-medium">
                            <x-user-avatar :user="$feedback->user" />
                            {{ $feedback->user->name }}
                        </span>
                        <span class="px-2 text-gray-400">|</span>
                        {{ $feedback->created_at->format('Y-m-d g:i A') }}
                        ({{ $feedback->created_at->diffForHumans() }})
                    </span>
                </div>
                <div class="w-full prose-sm prose max-w-none dark:prose-invert">
                    {!! Str::markdown($feedback->content) !!}
                </div>
            </div>
            @endforeach

            @if($record->feedbacks->count() === 0 && !$this->canGiveFeedback())
            <div class="py-8 text-center text-gray-400 dark:text-gray-500">
                {{ __('No feedback yet.') }}
            </div>
            @endif
            @endif

            {{-- Attachments Tab --}}
            @if($tab === 'attachments')
            <div class="flex flex-col gap-3">
                @forelse($record->getMedia('attachments') as $media)
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        @if(str_contains($media->mime_type, 'pdf'))
                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                        <div>
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $media->file_name }}</div>
                            <div class="text-xs text-gray-400">{{ number_format($media->size / 1024, 1) }} KB</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(str_contains($media->mime_type, 'pdf'))
                        <a href="{{ $media->getUrl() }}" target="_blank"
                            class="px-3 py-1 text-sm font-medium rounded text-green-600 hover:text-green-700 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/10">
                            {{ __('View') }}
                        </a>
                        @else
                        <a href="https://docs.google.com/gview?url={{ urlencode($media->getUrl()) }}&embedded=true" target="_blank"
                            class="px-3 py-1 text-sm font-medium rounded text-green-600 hover:text-green-700 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/10">
                            {{ __('View') }}
                        </a>
                        @endif
                        <a href="{{ $media->getUrl() }}" download
                            class="px-3 py-1 text-sm font-medium rounded text-primary-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/10">
                            {{ __('Download') }}
                        </a>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-gray-400 dark:text-gray-500">
                    {{ __('No attachments.') }}
                </div>
                @endforelse
            </div>
            @endif

        </x-filament::card>

        {{-- Right Column: Sidebar --}}
        <x-filament::card class="flex flex-col w-full gap-4 md:w-1/3 h-fit">

            {{-- Author --}}
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Author') }}</span>
                <div class="flex items-center gap-2">
                    <x-user-avatar :user="$record->user" />
                    <span class="text-gray-700 dark:text-gray-300">{{ $record->user->name }}</span>
                </div>
            </div>

            {{-- Project --}}
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Project') }}</span>
                <span class="text-gray-700 dark:text-gray-300">{{ $record->project?->name ?? __('General') }}</span>
            </div>

            {{-- Week --}}
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Week') }}</span>
                <span class="text-gray-700 dark:text-gray-300">{{ $record->week_label }}</span>
            </div>

            {{-- Status --}}
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</span>
                <div>{!! $record->status_badge !!}</div>
            </div>

            {{-- Submitted --}}
            @if($record->submitted_at)
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Submitted') }}</span>
                <span class="text-gray-700 dark:text-gray-300">
                    {{ $record->submitted_at->format('Y-m-d g:i A') }}
                </span>
            </div>
            @endif

            {{-- Acknowledged --}}
            @if($record->acknowledged_at)
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Acknowledged') }}</span>
                <span class="text-gray-700 dark:text-gray-300">
                    {{ $record->acknowledgedByUser?->name ?? 'N/A' }}
                    <span class="text-xs text-gray-400">
                        ({{ $record->acknowledged_at->format('Y-m-d g:i A') }})
                    </span>
                </span>
            </div>
            @endif

            {{-- Feedback Count --}}
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Feedback') }}</span>
                <span class="text-gray-700 dark:text-gray-300">{{ $record->feedbacks->count() }} {{ __('responses') }}</span>
            </div>

            {{-- Created --}}
            <div class="flex flex-col gap-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created') }}</span>
                <span class="text-gray-500 dark:text-gray-400">
                    {{ $record->created_at->format('Y-m-d g:i A') }}
                    <span class="text-xs">({{ $record->created_at->diffForHumans() }})</span>
                </span>
            </div>

        </x-filament::card>

    </div>

</x-filament::page>
