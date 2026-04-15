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
    <div class="flex items-center justify-between w-full">
        @if($record['due_date'])
        <div class="record-due-date" style="margin: 0 0;">
            @if($record['due_date']->lt(now()))
            <span
                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 border border-red-200 rounded-full">
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1"></span>
                {{ $record['due_date']->format('M j') }} (OVERDUE)
            </span>
            @elseif($record['due_date']->isToday())
            <span
                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 border border-red-200 rounded-full">
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1 animate-pulse"></span>
                {{ $record['due_date']->format('M j') }} (TODAY)
            </span>
            @elseif($record['due_date']->diffInDays(now()) <= 3)
            <span
                class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 border border-yellow-200 rounded-full">
                <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1"></span>
                {{ $record['due_date']->format('M j') }} ({{ $record['due_date']->diffInDays(now()) }}d)
            </span>
            @else
            <span
                class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 border border-green-200 rounded-full">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                {{ $record['due_date']->format('M j') }} ({{ $record['due_date']->diffInDays(now()) }}d)
            </span>
            @endif
        </div>
        @endif
        @if($record['responsible'])
        <x-user-avatar :user="$record['responsible']" />
        @endif
    </div>
    <div class="record-footer">
        <div class="record-type-code">
            @php $epic = $record['epic']; @endphp
            @if($epic && $epic != "")
            <div class="inline-flex items-center px-1.5 py-0.5 text-[10px] leading-tight text-white bg-purple-500 rounded break-words"
                title="{{ __('Epic') }}: {{ $epic->name }}">
                {{ $epic->name }}
            </div>
            @endif
            <x-ticket-priority :priority="$record['priority']" />
            <x-ticket-type :type="$record['type']" />
        </div>
    </div>
    @if($record['relations']?->count())
    <div class="record-relations">
        @php
            $colorMap = [
                'primary' => ['bg' => 'rgba(59,130,246,0.15)', 'border' => 'rgba(59,130,246,0.3)', 'text' => '#60a5fa', 'hash' => 'rgba(96,165,250,0.5)'],
                'warning' => ['bg' => 'rgba(245,158,11,0.15)', 'border' => 'rgba(245,158,11,0.3)', 'text' => '#fbbf24', 'hash' => 'rgba(251,191,36,0.5)'],
                'danger'  => ['bg' => 'rgba(239,68,68,0.15)', 'border' => 'rgba(239,68,68,0.3)', 'text' => '#f87171', 'hash' => 'rgba(248,113,113,0.5)'],
            ];
            $grouped = collect($record['relations'])->groupBy('type');
        @endphp
        @foreach($grouped as $type => $relations)
            @php $colors = $colorMap[config('system.tickets.relations.colors.' . $type)] ?? $colorMap['primary']; @endphp
            <div class="flex flex-wrap items-center gap-1">
                <span style="color: {{ $colors['text'] }}; font-size: 9px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ __(config('system.tickets.relations.list.' . $type)) }}
                </span>
                @foreach($relations as $relation)
                <a target="_blank" title="{{ $relation->relation->name ?? '' }}"
                    href="{{ route('filament.resources.tickets.share', $relation->relation->code) }}"
                    style="display:inline-flex;align-items:center;gap:2px;padding:1px 6px;border-radius:8px;font-size:10px;font-weight:600;text-decoration:none;background:{{ $colors['bg'] }};color:{{ $colors['text'] }};border:1px solid {{ $colors['border'] }};transition:background 0.15s;">
                    <span style="opacity:0.5;font-weight:400;">#</span>{{ $relation->relation->code }}
                </a>
                @endforeach
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