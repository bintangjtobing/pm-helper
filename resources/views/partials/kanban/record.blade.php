<div class="kanban-record" data-id="{{ $record['id'] }}" data-ticket-id="{{ $record['id'] }}">
    <div class="absolute z-10 text-gray-400 cursor-move handle top-2 right-2 hover:text-gray-600" title="Drag to move">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <circle cx="5" cy="5" r="1" />
            <circle cx="10" cy="5" r="1" />
            <circle cx="15" cy="5" r="1" />
            <circle cx="5" cy="10" r="1" />
            <circle cx="10" cy="10" r="1" />
            <circle cx="15" cy="10" r="1" />
            <circle cx="5" cy="15" r="1" />
            <circle cx="10" cy="15" r="1" />
            <circle cx="15" cy="15" r="1" />
        </svg>
    </div>
    <div class="record-info">
        @if($this->isMultiProject())
        <span class="record-subtitle">
            {{ $record['project']->name }}
        </span>
        @endif
        <div class="record-title">
            <span class="code">{{ $record['code'] }}</span>
            <span class="title">{{ $record['title'] }}</span>
        </div>
    </div>
    @if($record['due_date'])
    <div class="record-due-date" style="margin: 0 0;">
        @if($record['due_date']->lt(now()))
        {{-- Overdue - Red badge --}}
        <span
            class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 border border-red-200 rounded-full">
            <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1"></span>
            {{ $record['due_date']->format('M j') }} (OVERDUE)
        </span>
        @elseif($record['due_date']->isToday())
        {{-- Due today - Red badge with pulse --}}
        <span
            class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 border border-red-200 rounded-full">
            <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1 animate-pulse"></span>
            {{ $record['due_date']->format('M j') }} (TODAY)
        </span>
        @elseif($record['due_date']->diffInDays(now()) <= 3) {{-- Due soon (within 3 days) - Yellow badge --}} <span
            class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 border border-yellow-200 rounded-full">
            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1"></span>
            {{ $record['due_date']->format('M j') }} ({{ $record['due_date']->diffInDays(now()) }}d)
            </span>
            @else
            {{-- Normal - Green badge --}}
            <span
                class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 border border-green-200 rounded-full">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                {{ $record['due_date']->format('M j') }} ({{ $record['due_date']->diffInDays(now()) }}d)
            </span>
            @endif
    </div>
    @endif
    <div class="record-footer">
        <div class="record-type-code">
            @php($epic = $record['epic'])
            @if($epic && $epic != "")
            <div class="flex items-center justify-center px-2 py-1 text-xs text-center text-white bg-purple-500 rounded"
                title="{{ __('Epic') }}">
                {{ $epic->name }}
            </div>
            @endif
            <x-ticket-priority :priority="$record['priority']" />
            <x-ticket-type :type="$record['type']" />
        </div>
        @if($record['responsible'])
        <x-user-avatar :user="$record['responsible']" />
        @endif
    </div>
    @if($record['relations']?->count())
    <div class="record-relations">
        @foreach($record['relations'] as $relation)
        <div>
            <span class="type text-{{ config('system.tickets.relations.colors.' . $relation->type) }}-600">
                {{ __(config('system.tickets.relations.list.' . $relation->type)) }}
            </span>
            <a target="_blank" class="relation"
                href="{{ route('filament.resources.tickets.share', $relation->relation->code) }}">
                {{ $relation->relation->code }}
            </a>
        </div>
        @endforeach
    </div>
    @endif
    @if($record['totalLoggedHours'])
    <div class="record-logged-hours">
        <x-heroicon-o-clock class="w-4 h-4" /> {{ $record['totalLoggedHours'] }}
    </div>
    @endif
</div>