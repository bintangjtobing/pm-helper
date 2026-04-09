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
            @php
                $summary = $record->auto_summary;
                $progress = $summary['progress_summary'] ?? [];
                $ticketsUpdated = $progress['total_tickets_touched'] ?? count($summary['tickets_updated'] ?? []);
                $ticketsChangedThisWeek = $progress['tickets_updated_this_week'] ?? count($summary['tickets_changed_this_week'] ?? []);
                $ticketsCompleted = $progress['tickets_completed'] ?? count($summary['tickets_completed'] ?? []);
                $statusChanges = $progress['status_changes_count'] ?? count($summary['status_changes'] ?? []);
                $totalHours = $progress['total_hours'] ?? ($summary['hours_logged']['total_hours'] ?? 0);
                $completionRate = $progress['completion_rate'] ?? 0;
                $projectBreakdown = $summary['project_breakdown'] ?? [];
                $statusBreakdown = $summary['status_breakdown'] ?? [];
                $typeBreakdown = $summary['type_breakdown'] ?? [];
            @endphp

            {{-- 1. Progress Summary Table (like PDF) --}}
            <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Progress Summary') }}</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-4 py-2 text-xs font-semibold text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Metric') }}</th>
                            <th class="px-4 py-2 text-xs font-semibold text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Value') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ __('Total Tickets') }}</td>
                            <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200">{{ $ticketsUpdated }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ __('Updated This Week') }}</td>
                            <td class="px-4 py-2 font-medium text-blue-600 dark:text-blue-400">{{ $ticketsChangedThisWeek }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ __('Tickets Completed') }}</td>
                            <td class="px-4 py-2 font-medium text-green-600 dark:text-green-400">{{ $ticketsCompleted }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ __('Completion Rate') }}</td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 max-w-[120px] overflow-hidden bg-gray-200 rounded-full dark:bg-gray-700">
                                        <div class="h-full rounded-full {{ $completionRate >= 80 ? 'bg-green-500' : ($completionRate >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ min($completionRate, 100) }}%"></div>
                                    </div>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $completionRate }}%</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ __('Status Changes') }}</td>
                            <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200">{{ $statusChanges }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ __('Hours Logged') }}</td>
                            <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200">{{ $totalHours }}h</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ __('Projects Worked') }}</td>
                            <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200">{{ count($projectBreakdown) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- 2. Breakdown Row: Status + Type side by side --}}
            @if(!empty($statusBreakdown) || !empty($typeBreakdown))
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @if(!empty($statusBreakdown))
                <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                    <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                        <h4 class="text-xs font-semibold text-gray-600 uppercase dark:text-gray-400">{{ __('By Status') }}</h4>
                    </div>
                    <div class="p-3 space-y-2">
                        @foreach($statusBreakdown as $status => $count)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ $status }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 rounded-full dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ $count }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($typeBreakdown))
                <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                    <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                        <h4 class="text-xs font-semibold text-gray-600 uppercase dark:text-gray-400">{{ __('By Type') }}</h4>
                    </div>
                    <div class="p-3 space-y-2">
                        @foreach($typeBreakdown as $type => $count)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ $type }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 rounded-full dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ $count }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- 3. Tickets by Project --}}
            @if(!empty($projectBreakdown))
            @foreach($projectBreakdown as $projectName => $projectData)
            <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                    <h4 class="text-xs font-semibold text-gray-600 uppercase dark:text-gray-400">{{ $projectName }}</h4>
                    <span class="px-2 py-0.5 text-xs font-medium bg-primary-100 text-primary-700 rounded-full dark:bg-primary-900/30 dark:text-primary-400">{{ $projectData['total'] }} {{ __('tickets') }}</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach(array_slice($projectData['tickets'], 0, 15) as $ticket)
                    <a href="{{ route('filament.resources.tickets.view', $ticket['id']) }}" class="flex items-center gap-2 px-4 py-2 text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <span class="px-1.5 py-0.5 text-xs font-mono bg-gray-100 text-gray-600 rounded dark:bg-gray-700 dark:text-gray-400 shrink-0">{{ $ticket['code'] }}</span>
                        <span class="text-gray-700 truncate dark:text-gray-300 hover:text-primary-500">{{ $ticket['name'] }}</span>
                        <span class="ml-auto px-1.5 py-0.5 text-xs rounded shrink-0" style="background-color: {{ $ticket['status_color'] ?? '#6b7280' }}20; color: {{ $ticket['status_color'] ?? '#6b7280' }}">{{ $ticket['status'] }}</span>
                    </a>
                    @endforeach
                    @if(count($projectData['tickets']) > 15)
                    <div class="px-4 py-2 text-xs text-gray-400">...{{ __('and') }} {{ count($projectData['tickets']) - 15 }} {{ __('more') }}</div>
                    @endif
                </div>
            </div>
            @endforeach
            @endif

            {{-- 4. Time Log Detail --}}
            @if(!empty($summary['hours_logged']['entries']))
            <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                    <h4 class="text-xs font-semibold text-gray-600 uppercase dark:text-gray-400">{{ __('Time Log') }}</h4>
                    <span class="px-2 py-0.5 text-xs font-medium bg-orange-100 text-orange-700 rounded-full dark:bg-orange-900/30 dark:text-orange-400">{{ $totalHours }}h {{ __('total') }}</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach(array_slice($summary['hours_logged']['entries'], 0, 15) as $entry)
                    <a href="{{ $entry['ticket_id'] ? route('filament.resources.tickets.view', $entry['ticket_id']) : '#' }}" class="flex items-center gap-2 px-4 py-2 text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <span class="px-1.5 py-0.5 text-xs font-mono bg-gray-100 text-gray-600 rounded dark:bg-gray-700 dark:text-gray-400 shrink-0">{{ $entry['ticket_code'] }}</span>
                        <span class="text-gray-700 truncate dark:text-gray-300 hover:text-primary-500">{{ $entry['ticket_name'] }}</span>
                        <span class="ml-auto font-medium text-orange-600 shrink-0 dark:text-orange-400">{{ $entry['hours'] }}h</span>
                        @if($entry['activity'])
                        <span class="text-xs text-gray-400 shrink-0">({{ $entry['activity'] }})</span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
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
                    {!! \App\Helpers\CodeBlockHelper::autoLinkUrls(Str::markdown(\App\Helpers\CodeBlockHelper::autoDetectCodeBlocks($feedback->content))) !!}
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

            {{-- Viewed By --}}
            @if($record->status !== 'draft')
            @php
                $reportViews = $record->views()->with('user')->latest('viewed_at')->get();
            @endphp
            <div class="flex flex-col gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ __('Viewed by') }}
                    @if($reportViews->count() > 0)
                    <span class="px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ $reportViews->count() }}
                    </span>
                    @endif
                </span>
                @forelse($reportViews as $view)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-user-avatar :user="$view->user" />
                        <span class="text-xs text-gray-700 dark:text-gray-300">{{ $view->user->name }}</span>
                    </div>
                    <span class="text-xs text-gray-400" title="{{ $view->viewed_at->format('Y-m-d g:i A') }}">
                        {{ $view->viewed_at->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="py-2 text-xs text-center text-gray-400 dark:text-gray-500">
                    {{ __('No one has viewed this report yet.') }}
                </div>
                @endforelse
            </div>
            @endif

            {{-- Attachments --}}
            <div class="flex flex-col gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ __('Attachments') }}
                    @if($record->getMedia('attachments')->count() > 0)
                    <span class="px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ $record->getMedia('attachments')->count() }}
                    </span>
                    @endif
                </span>
                @forelse($record->getMedia('attachments') as $media)
                <div class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg dark:border-gray-700">
                    @if(str_contains($media->mime_type, 'pdf'))
                    <svg class="w-6 h-6 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                    </svg>
                    @elseif(str_contains($media->mime_type, 'image'))
                    <svg class="w-6 h-6 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                    </svg>
                    @else
                    <svg class="w-6 h-6 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                    </svg>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium text-gray-700 truncate dark:text-gray-300">{{ $media->file_name }}</div>
                        <div class="text-xs text-gray-400">{{ number_format($media->size / 1024, 1) }} KB</div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        @if(str_contains($media->mime_type, 'pdf'))
                        <a href="{{ $media->getUrl() }}" target="_blank" title="{{ __('View') }}"
                            class="p-1 text-green-600 rounded hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @else
                        <a href="https://docs.google.com/gview?url={{ urlencode($media->getUrl()) }}&embedded=true" target="_blank" title="{{ __('View') }}"
                            class="p-1 text-green-600 rounded hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @endif
                        <a href="{{ $media->getUrl() }}" download title="{{ __('Download') }}"
                            class="p-1 rounded text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    </div>
                </div>
                @empty
                <div class="py-3 text-xs text-center text-gray-400 dark:text-gray-500">
                    {{ __('No attachments.') }}
                </div>
                @endforelse
            </div>

        </x-filament::card>

    </div>

</x-filament::page>
