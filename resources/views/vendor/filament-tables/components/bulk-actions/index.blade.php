@props([
    'actions',
])

<div {{ $attributes->class(['filament-tables-bulk-actions flex flex-wrap items-center gap-2']) }}>
    @foreach ($actions as $action)
        {{ $action }}
    @endforeach
</div>
