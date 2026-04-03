@php
    $record = $getRecord();
@endphp
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2">
        @if($record->project)
        <span class="px-1.5 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded bg-primary-500/10 text-primary-500">
            {{ $record->project->name }}
        </span>
        @endif
        @if($record->type)
        <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase">
            {{ $record->type->name }}
        </span>
        @endif
    </div>
    <div class="flex items-center gap-2">
        <span class="text-xs font-mono text-gray-400 dark:text-gray-500 shrink-0">{{ $record->code }}</span>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $record->name }}</span>
    </div>
</div>
