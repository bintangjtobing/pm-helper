<x-filament::page>
    <style>
        /* Tighten the default filament page container for the fullpage messenger */
        body.messenger-fullpage .filament-main { background: #0b0f17; }
        .filament-page .space-y-6 > :not([hidden]) ~ :not([hidden]) { margin: 0 !important; }
    </style>
    <div class="-m-4 sm:-m-6 md:-m-8">
        @livewire('messenger', ['mode' => 'fullpage'])
    </div>
</x-filament::page>
