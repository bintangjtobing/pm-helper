<x-tables::button
    x-on:click="mountBulkAction('{{ $getName() }}')"
    :icon="$getIcon()"
    :color="$getColor() ?? 'secondary'"
    size="sm"
    tag="button"
    type="button"
    class="filament-tables-bulk-action"
>
    {{ $getLabel() }}
</x-tables::button>
