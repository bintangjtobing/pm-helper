<x-filament::page>
    <style>
        /* Kill Filament page chrome so messenger fills edge-to-edge
           under the topbar, flush with the sidebar. */
        .filament-main { gap: 0 !important; }
        .filament-main-content {
            padding: 0 !important;
            max-width: none !important;
            margin: 0 !important;
        }
        .filament-main-footer { display: none !important; }
        .filament-page { padding: 0 !important; }
        .filament-page > .space-y-6 > :not([hidden]) ~ :not([hidden]) { margin: 0 !important; }
        .filament-page > .space-y-6 { gap: 0 !important; }
    </style>
    @livewire('messenger', ['mode' => 'fullpage'])
</x-filament::page>
