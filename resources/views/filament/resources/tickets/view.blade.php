@php
    $record = $this->record;
    use Illuminate\Support\Str;
@endphp
<x-filament::page>

    <a href="{{ route('filament.pages.kanban/{project}', ['project' => $record->project->id]) }}"
        class="flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-gray-700">
        <x-heroicon-o-arrow-left class="w-4 h-4" /> {{ __('Back to kanban board') }}
    </a>

    <div class="flex flex-col w-full gap-5 md:flex-row">

        <x-filament::card class="flex flex-col w-full gap-5 md:w-2/3">
            <div class="flex flex-col w-full gap-0">
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1 text-sm font-medium text-primary-500">
                        <x-heroicon-o-ticket class="w-4 h-4" />
                        {{ $record->code }}
                    </span>
                    <span class="text-sm font-light text-gray-400">|</span>
                    <span class="flex items-center gap-1 text-sm text-gray-500">
                        {{ $record->project->name }}
                    </span>
                </div>
                <span class="text-xl text-gray-700">
                    {{ $record->name }}
                </span>
            </div>
            <div class="flex items-center w-full gap-2">
                <div class="flex items-center justify-center px-2 py-1 text-xs text-center text-white rounded"
                    style="background-color: {{ $record->status->color }};">
                    {{ $record->status->name }}
                </div>
                <div class="flex items-center justify-center px-2 py-1 text-xs text-center text-white rounded"
                    style="background-color: {{ $record->priority->color }};">
                    {{ $record->priority->name }}
                </div>
                <div class="flex items-center justify-center px-2 py-1 text-xs text-center text-white rounded"
                    style="background-color: {{ $record->type->color }};">
                    <x-icon class="h-3 text-white" name="{{ $record->type->icon }}" />
                    <span class="ml-2">
                        {{ $record->type->name }}
                    </span>
                </div>
            </div>

            {{-- 📝 OPTIMIZED DESCRIPTION SECTION --}}
            <div class="flex flex-col w-full gap-2">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Content') }}
                </span>
                <div
                    class="w-full p-4 overflow-hidden prose-sm prose border border-gray-200 rounded-lg max-w-none bg-gray-50">
                    <div class="leading-relaxed text-gray-700">
                        {!! $record->rendered_content !!}
                    </div>

                    {{-- Bug Report Details --}}
                    @if($record->isBugType() && ($record->steps_to_reproduce || $record->expected_behavior || $record->actual_behavior || $record->environment))
                    <div class="mt-4 p-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/10">
                        <h4 class="text-sm font-semibold text-red-700 dark:text-red-400 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ __('Bug Report Details') }}
                        </h4>

                        @if($record->steps_to_reproduce)
                        <div class="mb-3">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">{{ __('Steps to Reproduce') }}</span>
                            <div class="mt-1 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $record->steps_to_reproduce }}</div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @if($record->expected_behavior)
                            <div class="p-3 rounded bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800">
                                <span class="text-xs font-medium text-green-700 dark:text-green-400 uppercase tracking-wide">{{ __('Expected Behavior') }}</span>
                                <div class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $record->expected_behavior }}</div>
                            </div>
                            @endif

                            @if($record->actual_behavior)
                            <div class="p-3 rounded bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800">
                                <span class="text-xs font-medium text-red-700 dark:text-red-400 uppercase tracking-wide">{{ __('Actual Behavior') }}</span>
                                <div class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $record->actual_behavior }}</div>
                            </div>
                            @endif
                        </div>

                        @if($record->environment)
                        <div class="mt-3">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">{{ __('Environment') }}</span>
                            <div class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $record->environment }}</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </x-filament::card>

        <x-filament::card class="flex flex-col w-full md:w-1/3">
            <div class="flex flex-col w-full gap-1" wire:ignore>
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Owner') }}
                </span>
                <div class="flex items-center w-full gap-1 text-gray-500">
                    <x-user-avatar :user="$record->owner" />
                    {{ $record->owner->name }}
                </div>
            </div>

            <div class="flex flex-col w-full gap-1 pt-3" wire:ignore>
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Responsible') }}
                </span>
                <div class="flex items-center w-full gap-1 text-gray-500">
                    @if($record->responsible)
                    <x-user-avatar :user="$record->responsible" />
                    @endif
                    {{ $record->responsible?->name ?? '-' }}
                </div>
            </div>

            @php
                $qaActivity = \App\Models\TicketActivity::where('ticket_id', $record->id)
                    ->whereHas('newStatus', fn ($q) => $q->whereIn('name', ['QA Passed', 'QA Failed', 'Retest']))
                    ->with(['user', 'newStatus'])
                    ->latest()
                    ->first();
            @endphp
            @if($qaActivity && $qaActivity->user)
            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('QA Reviewer') }}
                </span>
                <div class="flex items-center w-full gap-1 text-gray-500">
                    <x-user-avatar :user="$qaActivity->user" />
                    {{ $qaActivity->user->name }}
                </div>
                <span class="text-xs text-gray-400">
                    {{ $qaActivity->newStatus->name }} &middot; {{ $qaActivity->created_at->diffForHumans() }}
                </span>
            </div>
            @endif

            @if($record->project->type === 'scrum')
            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Sprint') }}
                </span>
                <div class="flex flex-col justify-center w-full gap-1 text-gray-500">
                    @if($record->sprint)
                    {{ $record->sprint->name }}
                    <span class="text-xs text-gray-400">
                        {{ __('Starts at:') }} {{ $record->sprint->starts_at->format(__('Y-m-d')) }} -
                        {{ __('Ends at:') }} {{ $record->sprint->ends_at->format(__('Y-m-d')) }}
                    </span>
                    @else
                    -
                    @endif
                </div>
            </div>
            @else
            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Epic') }}
                </span>
                <div class="flex items-center w-full gap-1 text-gray-500">
                    @if($record->epic)
                    {{ $record->epic->name }}
                    @else
                    -
                    @endif
                </div>
            </div>
            @endif

            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Estimation') }}
                </span>
                <div class="flex items-center w-full gap-1 text-gray-500">
                    @if($record->estimation)
                    {{ $record->estimationForHumans }}
                    @else
                    -
                    @endif
                </div>
            </div>

            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Due Date') }}
                </span>
                <div class="w-full">
                    @if($record->due_date)
                    @if($record->due_date->lt(now()))
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span>
                        {{ $record->due_date->format('M d, Y') }} (OVERDUE)
                    </span>
                    @elseif($record->due_date->isToday())
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5 animate-pulse"></span>
                        {{ $record->due_date->format('M d, Y') }} (DUE TODAY!)
                    </span>
                    @elseif($record->due_date->diffInDays(now()) <= 3)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-1.5"></span>
                        {{ $record->due_date->format('M d, Y') }} ({{ $record->due_date->diffInDays(now()) }} days left)
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                        {{ $record->due_date->format('M d, Y') }} ({{ $record->due_date->diffInDays(now()) }} days left)
                    </span>
                    @endif
                    @else
                    <span class="text-gray-400">No due date set</span>
                    @endif
                </div>
            </div>

            @if($record->isCompleted() && $record->completedAt)
            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Completed Date') }}
                </span>
                <div class="w-full">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span>
                        {{ $record->completedAt->format('M d, Y \a\t g:i A') }}
                    </span>
                    <div class="mt-1 text-xs text-gray-500">
                        ({{ $record->completedAt->diffForHumans() }})
                    </div>
                </div>
            </div>
            @endif

            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Total time logged') }}
                </span>
                @if($record->hours()->count())
                @if($record->estimation)
                <div class="flex justify-between mb-1">
                    <span
                        class="text-base font-medium text-{{ $record->estimationProgress > 100 ? 'danger' : 'primary' }}-700">
                        {{ $record->totalLoggedHours }}
                    </span>
                    <span
                        class="text-sm font-medium text-{{ $record->estimationProgress > 100 ? 'danger' : 'primary' }}-700">
                        {{ round($record->estimationProgress) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-{{ $record->estimationProgress > 100 ? 'danger' : 'primary' }}-600 h-2.5 rounded-full"
                        style="width: {{ $record->estimationProgress > 100 ? 100 : $record->estimationProgress }}%">
                    </div>
                </div>
                @else
                <div class="flex items-center w-full gap-1 text-gray-500">
                    {{ $record->totalLoggedHours }}
                </div>
                @endif
                @else
                -
                @endif
            </div>

            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Subscribers') }}
                </span>
                <div class="flex items-center w-full gap-1 text-gray-500">
                    @if($record->subscribers->count())
                    @foreach($record->subscribers as $subscriber)
                    <x-user-avatar :user="$subscriber" />
                    @endforeach
                    @else
                    {{ '-' }}
                    @endif
                </div>
            </div>

            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('CC Users') }}
                </span>
                <div class="flex items-center w-full gap-1 text-gray-500">
                    @if($record->ccUsers->count())
                    @foreach($record->ccUsers as $ccUser)
                    <x-user-avatar :user="$ccUser" />
                    @endforeach
                    @else
                    {{ '-' }}
                    @endif
                </div>
            </div>

            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Creation date') }}
                </span>
                <div class="w-full text-gray-500">
                    {{ $record->created_at->format(__('Y-m-d g:i A')) }}
                    <span class="text-xs text-gray-400">
                        ({{ $record->created_at->diffForHumans() }})
                    </span>
                </div>
            </div>

            <div class="flex flex-col w-full gap-1 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Last update') }}
                </span>
                <div class="w-full text-gray-500">
                    {{ $record->updated_at->format(__('Y-m-d g:i A')) }}
                    <span class="text-xs text-gray-400">
                        ({{ $record->updated_at->diffForHumans() }})
                    </span>
                </div>
            </div>

            @if($record->relations->count())
            <div class="flex flex-col w-full gap-2 pt-3">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('Ticket relations') }}
                </span>
                @php
                    $colorMap = [
                        'primary' => ['bg' => 'rgba(59,130,246,0.15)', 'border' => 'rgba(59,130,246,0.3)', 'text' => '#60a5fa', 'type_bg' => '#2563eb'],
                        'warning' => ['bg' => 'rgba(245,158,11,0.15)', 'border' => 'rgba(245,158,11,0.3)', 'text' => '#fbbf24', 'type_bg' => '#d97706'],
                        'danger'  => ['bg' => 'rgba(239,68,68,0.15)', 'border' => 'rgba(239,68,68,0.3)', 'text' => '#f87171', 'type_bg' => '#dc2626'],
                    ];
                    $grouped = $record->relations->groupBy('type');
                @endphp
                @foreach($grouped as $type => $relations)
                    @php $colors = $colorMap[config('system.tickets.relations.colors.' . $type)] ?? $colorMap['primary']; @endphp
                    <div class="flex flex-col gap-1.5">
                        <span style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: {{ $colors['text'] }};">
                            {{ __(config('system.tickets.relations.list.' . $type)) }}
                        </span>
                        @foreach($relations as $relation)
                        <a target="_blank"
                            href="{{ route('filament.resources.tickets.share', $relation->relation->code) }}"
                            style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;background:{{ $colors['bg'] }};border:1px solid {{ $colors['border'] }};text-decoration:none;transition:background 0.15s;">
                            <span style="display:inline-flex;align-items:center;gap:2px;padding:1px 6px;border-radius:6px;font-size:11px;font-weight:700;background:{{ $colors['border'] }};color:{{ $colors['text'] }};white-space:nowrap;">
                                <span style="opacity:0.5;font-weight:400;">#</span>{{ $relation->relation->code }}
                            </span>
                            <span style="font-size:12px;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ \Illuminate\Support\Str::limit($relation->relation->name ?? '', 40) }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
            @endif
        </x-filament::card>

    </div>

    <div class="flex flex-col w-full gap-5 md:flex-row">

        <x-filament::card class="flex flex-col w-full md:w-2/3">
            <div class="flex items-center w-full gap-2">
                <button wire:click="selectTab('comments')"
                    class="text-sm font-medium px-3 py-2 border-b-2 border-transparent hover:border-primary-500 flex items-center gap-1 @if($tab === 'comments') border-primary-500 text-primary-500 @else text-gray-500 @endif">
                    {{ __('Comments') }}
                </button>
                <button wire:click="selectTab('activities')"
                    class="text-sm font-medium px-3 py-2 border-b-2 border-transparent hover:border-primary-500 @if($tab === 'activities') border-primary-500 text-primary-500 @else text-gray-500 @endif">
                    {{ __('Activities') }}
                </button>
                <button wire:click="selectTab('time')"
                    class="text-sm font-medium px-3 py-2 border-b-2 border-transparent hover:border-primary-500 @if($tab === 'time') border-primary-500 text-primary-500 @else text-gray-500 @endif">
                    {{ __('Time logged') }}
                </button>
                <button wire:click="selectTab('attachments')"
                    class="text-sm font-medium px-3 py-2 border-b-2 border-transparent hover:border-primary-500 @if($tab === 'attachments') border-primary-500 text-primary-500 @else text-gray-500 @endif">
                    {{ __('Attachments') }}
                </button>
                <button wire:click="selectTab('qa-checklist')"
                    class="text-sm font-medium px-3 py-2 border-b-2 border-transparent hover:border-primary-500 flex items-center gap-1 @if($tab === 'qa-checklist') border-primary-500 text-primary-500 @else text-gray-500 @endif">
                    {{ __('QA Checklist') }}
                    @php
                        $qaTotal = $record->qaChecklists()->count();
                        $qaPassed = $record->qaChecklists()->where('status', 'passed')->count();
                        $qaFailed = $record->qaChecklists()->where('status', 'failed')->count();
                    @endphp
                    @if($qaTotal > 0)
                    <span class="px-1.5 py-0.5 text-xs rounded-full {{ $qaFailed > 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : ($qaPassed === $qaTotal ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400') }}">
                        {{ $qaPassed }}/{{ $qaTotal }}
                    </span>
                    @endif
                </button>
            </div>

            @if($tab === 'comments')
            <form wire:submit.prevent="submitComment" class="pb-5">
                {{ $this->form }}

                {{-- Video / Screen Recording upload --}}
                @if(!$selectedCommentId)
                <div class="mt-3" x-data="{ dragOver: false }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Screen Recording / Video') }}
                        <span class="text-xs text-gray-400 font-normal">({{ __('.mp4, .mov — max 100 MB') }})</span>
                    </label>
                    <div
                        class="relative border-2 border-dashed rounded-lg p-4 text-center transition-colors"
                        :class="dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
                        x-on:dragover.prevent="dragOver = true"
                        x-on:dragleave.prevent="dragOver = false"
                        x-on:drop.prevent="dragOver = false; $refs.videoInput.files = $event.dataTransfer.files; $refs.videoInput.dispatchEvent(new Event('change', { bubbles: true }))"
                    >
                        <input
                            type="file"
                            wire:model="commentVideos"
                            x-ref="videoInput"
                            accept="video/mp4,video/quicktime,video/x-m4v,.mp4,.mov,.m4v"
                            multiple
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        >
                        <div class="flex flex-col items-center gap-1">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Click or drag video files here') }}</span>
                        </div>
                    </div>

                    {{-- Upload progress --}}
                    <div wire:loading wire:target="commentVideos" class="mt-2">
                        <div class="flex items-center gap-2 text-xs text-primary-600 dark:text-primary-400">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ __('Uploading video...') }}
                        </div>
                    </div>

                    {{-- Preview uploaded videos --}}
                    @if(!empty($commentVideos))
                    <div class="mt-2 space-y-2">
                        @foreach($commentVideos as $idx => $video)
                            @if($video instanceof \Livewire\TemporaryUploadedFile)
                            <div class="flex items-center justify-between gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $video->getClientOriginalName() }}</span>
                                    <span class="text-xs text-gray-400 flex-shrink-0">
                                        {{ round($video->getSize() / 1048576, 1) }} MB
                                    </span>
                                </div>
                                <button type="button" wire:click="removeVideo({{ $idx }})"
                                    class="text-red-500 hover:text-red-700 flex-shrink-0" title="{{ __('Remove') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @endif
                    @error('commentVideos.*')
                        <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div class="flex items-center gap-2 mt-3">
                    <button type="submit" class="px-3 py-2 text-white rounded bg-primary-500 hover:bg-primary-600">
                        {{ __($selectedCommentId ? 'Edit comment' : 'Add comment') }}
                    </button>
                    @if($selectedCommentId)
                    <button type="button" wire:click="cancelEditComment"
                        class="px-3 py-2 text-white rounded bg-warning-500 hover:bg-warning-600">
                        {{ __('Cancel') }}
                    </button>
                    @endif
                </div>
            </form>

            @foreach($record->comments->sortByDesc('created_at') as $comment)
            <div
                class="w-full flex flex-col gap-2 @if(!$loop->last) pb-5 mb-5 border-b border-gray-200 @endif ticket-comment">
                <div class="flex justify-between w-full">
                    <span class="flex items-center gap-1 text-sm text-gray-500">
                        <span class="flex items-center gap-1 font-medium">
                            <x-user-avatar :user="$comment->user" />
                            {{ $comment->user->name }}
                        </span>
                        <span class="px-2 text-gray-400">|</span>
                        {{ $comment->created_at->format('Y-m-d g:i A') }}
                        ({{ $comment->created_at->diffForHumans() }})
                    </span>
                    <div class="flex items-center gap-2 actions">
                        <button type="button" wire:click="raiseToDiscussion({{ $comment->id }})"
                            class="inline-flex items-center gap-1 text-xs text-amber-500 hover:text-amber-600 hover:underline"
                            title="{{ __('Raise this comment to an open discussion') }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            {{ __('Raise') }}
                        </button>
                        @if($this->isAdministrator() || $comment->user_id === auth()->user()->id)
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <button type="button" wire:click="editComment({{ $comment->id }})"
                            class="text-xs text-primary-500 hover:text-primary-600 hover:underline">
                            {{ __('Edit') }}
                        </button>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <button type="button" wire:click="deleteComment({{ $comment->id }})"
                            class="text-xs text-danger-500 hover:text-danger-600 hover:underline">
                            {{ __('Delete') }}
                        </button>
                        @endif
                    </div>
                </div>
                <div class="w-full prose-sm prose max-w-none dark:prose-invert">
                    {!! \App\Helpers\CodeBlockHelper::linkTicketCodes(\App\Helpers\MentionHelper::renderMentions(\App\Helpers\CodeBlockHelper::renderContent($comment->content))) !!}
                </div>

                {{-- Video / Screen Recording attachments --}}
                @if($comment->attachments->count())
                <div class="mt-3 space-y-3">
                    @foreach($comment->attachments as $att)
                        <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                            <video
                                controls
                                preload="metadata"
                                class="w-full max-h-[400px] bg-black"
                                style="max-width: 640px;"
                            >
                                <source src="{{ asset('storage/' . $att->filename_stored) }}" type="video/mp4">
                                {{ __('Your browser does not support the video tag.') }}
                            </video>
                            <div class="flex items-center justify-between px-3 py-2 bg-gray-100 dark:bg-gray-700">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-xs text-gray-600 dark:text-gray-300 truncate">{{ $att->filename_original }}</span>
                                    <span class="text-xs text-gray-400">
                                        {{ $att->size_bytes >= 1048576 ? round($att->size_bytes / 1048576, 1) . ' MB' : round($att->size_bytes / 1024, 1) . ' KB' }}
                                    </span>
                                </div>
                                <a href="{{ asset('storage/' . $att->filename_stored) }}" download="{{ $att->filename_original }}"
                                    class="flex items-center gap-1 text-xs text-primary-500 hover:text-primary-600 flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    {{ __('Download') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
            @endif

            @if($tab === 'activities')
            <div class="flex flex-col w-full pt-5">
                @if($record->activities->count())
                @foreach($record->activities->sortByDesc('created_at') as $activity)
                <div class="w-full flex flex-col gap-2 @if(!$loop->last) pb-5 mb-5 border-b border-gray-200 @endif">
                    <span class="flex items-center gap-1 text-sm text-gray-500">
                        <span class="flex items-center gap-1 font-medium">
                            <x-user-avatar :user="$activity->user" />
                            {{ $activity->user->name }}
                        </span>
                        <span class="px-2 text-gray-400">•</span>
                        {{ $activity->formattedDate }}
                        <span class="text-xs text-gray-400">
                            ({{ $activity->created_at->diffForHumans() }})
                        </span>
                    </span>
                    <div class="flex items-center w-full gap-3">
                        <span class="text-gray-400">{{ $activity->oldStatus->name }}</span>
                        <x-heroicon-o-arrow-right class="w-4 h-4 text-gray-400" />
                        <span style="color: {{ $activity->newStatus->color }}" class="font-medium">
                            {{ $activity->newStatus->name }}
                        </span>

                        @if($activity->newStatus->name === 'Completed')
                        <span
                            class="inline-flex items-center px-2 py-1 ml-2 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Completed
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
                @else
                <span class="text-sm font-medium text-gray-400">
                    {{ __('No activities yet!') }}
                </span>
                @endif
            </div>
            @endif

            @if($tab === 'time')
            <livewire:timesheet.time-logged :ticket="$record" />
            @endif

            @if($tab === 'attachments')
            <livewire:ticket.attachments :ticket="$record" />
            @endif

            @if($tab === 'qa-checklist')
            <livewire:ticket.qa-checklist :ticket="$record" />
            @endif
        </x-filament::card>

        <div class="flex flex-col w-full md:w-1/3"></div>

    </div>

    @push('scripts')
    <script>
        window.addEventListener('shareTicket', (e) => {
            const text = e.detail.url;
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Unable to copy to clipboard', err);
            }
            document.body.removeChild(textArea);
            new Notification()
                .success()
                .title('{{ __('Url copied to clipboard') }}')
                .duration(6000)
                .send()
        });
    </script>
    <script>
        window.mentionUsers = {!! $this->getMentionUsersJs() !!};
        console.log('Mention users injected:', window.mentionUsers);
    </script>

    <script src="{{ asset('js/mentions.js') }}"></script>

    <style>
        .mentions-dropdown {
            font-family: inherit;
        }

        .mention-item:hover {
            background-color: #f3f4f6 !important;
        }

        .mention-item.selected {
            background-color: #f3f4f6 !important;
        }

        .mention {
            background-color: #e0f2fe;
            color: #0277bd;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: 500;
        }

        /* Prose styling moved to filament.scss for global use */
    </style>
    @endpush
</x-filament::page>
