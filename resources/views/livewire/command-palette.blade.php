@php
    $flat = [];
    foreach ($results as $category => $items) {
        foreach ($items as $item) {
            $flat[] = $item;
        }
    }
    $flatUrlsJson = json_encode(array_column($flat, 'url'));
@endphp

<div x-data="commandPalette({{ $flatUrlsJson }})" wire:ignore.self>

    {{-- Trigger button now lives inside the sidebar (registered via sidebar.start render hook).
         This component only owns the modal + keyboard wiring. The button dispatches a
         'cmd-palette:toggle' CustomEvent that our init() handler listens for. --}}

    {{-- Backdrop + Modal (NOT teleported — keeps wire:model bindings alive) --}}
    <div x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-start justify-center pt-20 px-4 bg-slate-900/70 backdrop-blur-sm"
         @click.self="close()"
         x-transition.opacity.duration.150ms>

            <div class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-black/5 overflow-hidden"
                 @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                {{-- Search input --}}
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-ref="searchInput"
                           wire:model.debounce.250ms="query"
                           @keydown.down.prevent="moveDown()"
                           @keydown.up.prevent="moveUp()"
                           @keydown.enter.prevent="selectCurrent()"
                           type="text"
                           placeholder="Search menus, tickets, projects, people, goals…"
                           class="flex-1 bg-transparent text-[14px] text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none border-0 p-0" />
                    <kbd class="hidden sm:inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-[10px] font-mono text-gray-500 dark:text-gray-400 shrink-0">Esc</kbd>
                </div>

                {{-- Loading indicator (visible while Livewire processes the search) --}}
                <div wire:loading wire:target="query" class="px-4 py-2 text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <svg class="inline-block w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Searching…
                </div>

                {{-- Results --}}
                <div class="max-h-[60vh] overflow-y-auto">
                    @php $globalIdx = 0; @endphp

                    @if(count($results) === 0)
                        <div class="px-6 py-12 text-center text-[13px] text-gray-500 dark:text-gray-400">
                            No matches for <strong class="text-gray-700 dark:text-gray-200">"{{ $query }}"</strong>.
                        </div>
                    @else
                        @foreach($results as $category => $items)
                            <div class="py-2">
                                <div class="px-4 pt-2 pb-1 text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    {{ $category }}
                                </div>
                                @foreach($items as $item)
                                    <a href="{{ $item['url'] }}"
                                       @click="close()"
                                       data-cmd-idx="{{ $globalIdx }}"
                                       :data-selected="selectedIndex === {{ $globalIdx }}"
                                       @mouseenter="selectedIndex = {{ $globalIdx }}"
                                       class="group/item flex items-center gap-3 px-4 py-2 transition-colors text-gray-700 dark:text-gray-300
                                              hover:bg-indigo-50 hover:text-gray-900 dark:hover:bg-indigo-500/15 dark:hover:text-gray-100
                                              data-[selected=true]:bg-indigo-100 data-[selected=true]:text-gray-900
                                              dark:data-[selected=true]:bg-indigo-500/25 dark:data-[selected=true]:text-gray-50
                                              data-[selected=true]:border-l-2 data-[selected=true]:border-indigo-500">
                                        <span class="shrink-0 w-7 h-7 rounded-md flex items-center justify-center bg-gray-100 dark:bg-gray-800 group-hover/item:bg-indigo-100 dark:group-hover/item:bg-indigo-500/25">
                                            @svg($item['icon'], 'w-4 h-4 text-gray-500 dark:text-gray-400 group-hover/item:text-indigo-600 dark:group-hover/item:text-indigo-300')
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-[13px] font-medium truncate">{{ $item['label'] }}</div>
                                            @if(! empty($item['group']))
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $item['group'] }}</div>
                                            @endif
                                        </div>
                                        <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500 shrink-0 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                            ↵ open
                                        </span>
                                    </a>
                                    @php $globalIdx++; @endphp
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-[10.5px] text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 font-mono">↑↓</kbd>
                            navigate
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 font-mono">↵</kbd>
                            open
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <kbd class="px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 font-mono">esc</kbd>
                            close
                        </span>
                    </div>
                    <div class="opacity-70">PM Helper · Command Palette</div>
                </div>
            </div>
    </div>
</div>

@once
    @push('scripts')
    @endpush
@endonce

<script>
    if (! window.commandPalette) {
        window.commandPalette = function (flatUrls) {
            return {
                open: false,
                selectedIndex: 0,
                flatUrls: flatUrls || [],
                isMac: /Mac|iPod|iPhone|iPad/.test(navigator.platform),

                init() {
                    // Ensure modal starts closed on every page load
                    this.open = false;

                    window.addEventListener('keydown', (e) => {
                        const cmd = e.metaKey || e.ctrlKey;
                        if (cmd && (e.key === 'k' || e.key === 'K')) {
                            e.preventDefault();
                            this.toggle();
                        }
                        if (e.key === 'Escape' && this.open) {
                            e.preventDefault();
                            this.close();
                        }
                    });

                    // Listen for the sidebar search button (dispatched from sidebar.start render hook)
                    window.addEventListener('cmd-palette:toggle', () => this.toggle());
                },

                toggle() {
                    this.open = !this.open;
                    this.selectedIndex = 0;
                    if (this.open) {
                        this.$nextTick(() => this.$refs.searchInput && this.$refs.searchInput.focus());
                    }
                },

                close() {
                    this.open = false;
                    this.selectedIndex = 0;
                    this.$wire.set('query', '');
                },

                moveDown() {
                    if (this.selectedIndex < this.flatUrls.length - 1) {
                        this.selectedIndex++;
                    }
                    this.$nextTick(() => this.scrollSelectedIntoView());
                },

                moveUp() {
                    if (this.selectedIndex > 0) {
                        this.selectedIndex--;
                    }
                    this.$nextTick(() => this.scrollSelectedIntoView());
                },

                scrollSelectedIntoView() {
                    const el = document.querySelector('[data-cmd-idx="' + this.selectedIndex + '"]');
                    if (el) el.scrollIntoView({ block: 'nearest' });
                },

                selectCurrent() {
                    const url = this.flatUrls[this.selectedIndex];
                    if (url) {
                        this.close();
                        window.location.href = url;
                    }
                }
            };
        };
    }
</script>
