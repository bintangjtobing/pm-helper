@php
    $record = $getRecord();
    $dueDate = $record->due_date;
    $estimation = $record->estimation;
    $isOverdue = $dueDate && $dueDate->isPast() && !in_array($record->status?->name, ['Complete', 'QA Passed', 'Closed']);
@endphp
<div class="flex flex-col gap-0.5">
    @if($dueDate)
    <span class="text-xs font-medium {{ $isOverdue ? 'text-red-500' : 'text-gray-700 dark:text-gray-300' }}">
        {{ $dueDate->format('M d, Y') }}
    </span>
    @if($isOverdue)
    <span class="text-[10px] font-medium text-red-400">{{ $dueDate->diffForHumans() }}</span>
    @endif
    @else
    <span class="text-xs text-gray-400">-</span>
    @endif
    @if($estimation)
    <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $estimation }}h {{ __('est.') }}</span>
    @endif
</div>
