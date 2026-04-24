@php
    $record = $getRecord();
    $user = \App\Models\User::find($record->user_id);
    $ticket = \App\Models\Ticket::with('project')->find($record->ticket_id);

    if (!$user) return;

    $avatarUrl = $user->getAttributes()['avatar_url']
        ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
    $timeAgo = $record->created_at->diffForHumans();
@endphp

@if($record->type === 'feedback')
    {{-- Customer Feedback --}}
    @php
        $feedback = \App\Models\CustomerFeedback::with('project')->find($record->ticket_id);
        $viewUrl = $feedback ? route('filament.resources.customer-feedbacks.view', $feedback->id) : '#';
    @endphp
    <div class="flex items-start gap-3 py-1 pl-2">
        <img src="{{ $avatarUrl }}" class="object-cover rounded-full w-9 h-9 shrink-0 ring-2 ring-amber-500/20" loading="lazy" />
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $record->getAttributes()['description'] ?? 'updated feedback' }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $timeAgo }}</span>
                    <a href="{{ $viewUrl }}" class="p-1 text-gray-400 rounded hover:text-primary-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 ring-1 ring-amber-500/20">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2z"/></svg>
                    {{ __('Customer Feedback') }}
                </span>
                @if($feedback && $feedback->project)
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500">{{ $feedback->project->name }}</span>
                @endif
                @if($feedback)
                    <span class="text-xs text-gray-500 truncate dark:text-gray-400">{{ \Illuminate\Support\Str::limit($feedback->title, 60) }}</span>
                @endif
            </div>
            @if($record->content)
                <div class="mt-1.5 px-3 py-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg border-l-2 border-amber-500/50">
                    <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($record->content, 150) }}</p>
                </div>
            @endif
        </div>
    </div>

@elseif($record->type === 'weekly_report')
    {{-- Weekly Report --}}
    @php $viewUrl = route('filament.resources.weekly-reports.view', $record->id); @endphp
    <div class="flex items-start gap-3 py-1 pl-2">
        <img src="{{ $avatarUrl }}" class="object-cover rounded-full w-9 h-9 shrink-0 ring-2 ring-purple-500/20" loading="lazy" />
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $record->getAttributes()['description'] ?? 'submitted weekly report' }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $timeAgo }}</span>
                    <a href="{{ $viewUrl }}" class="p-1 text-gray-400 rounded hover:text-primary-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide rounded-full bg-purple-500/10 text-purple-500 ring-1 ring-purple-500/20">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                    {{ __('Weekly Report') }}
                </span>
            </div>
        </div>
    </div>

@elseif($ticket)
    {{-- Activity or Comment --}}
    @php
        $viewUrl = route('filament.resources.tickets.share', $ticket->code);
        $descriptionText = $record->type === 'comment' ? __('added a comment') : ($record->getAttributes()['description'] ?? __('updated ticket'));
        $ringColor = $record->type === 'activity' ? 'ring-blue-500/20' : 'ring-green-500/20';
    @endphp
    <div class="flex items-start gap-3 py-1 pl-2">
        <img src="{{ $avatarUrl }}" class="object-cover rounded-full w-9 h-9 shrink-0 ring-2 {{ $ringColor }}" loading="lazy" />
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $descriptionText }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $timeAgo }}</span>
                    <a href="{{ $viewUrl }}" target="_blank" class="p-1 text-gray-400 rounded hover:text-primary-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-1.5 mt-0.5">
                <span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500">{{ $ticket->project->name }}</span>
                <span class="text-xs font-mono text-gray-400 dark:text-gray-500">{{ $ticket->code }}</span>
                <span class="text-xs text-gray-500 truncate dark:text-gray-400">{{ Str::limit($ticket->name, 60) }}</span>
            </div>

            {{-- Status change --}}
            @if($record->type === 'activity' && $record->old_status_id && $record->new_status_id)
                @php
                    $oldStatus = \App\Models\TicketStatus::withTrashed()->find($record->old_status_id);
                    $newStatus = \App\Models\TicketStatus::withTrashed()->find($record->new_status_id);
                @endphp
                @if($oldStatus && $newStatus)
                <div class="flex items-center gap-1.5 mt-1.5">
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
            @endif

            {{-- Comment preview --}}
            @if($record->type === 'comment' && $record->content)
            <div class="mt-1.5 px-3 py-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg border-l-2 border-green-500/50">
                <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">{{ Str::limit(trim(preg_replace('/\s+/', ' ', preg_replace(['/#{1,6}\s?/', '/\*{1,2}/', '/~~/', '/`{1,3}/'], '', str_replace(['\n', '\r'], ' ', strip_tags($record->content))))), 150) }}</p>
            </div>
            @endif
        </div>
    </div>
@endif
