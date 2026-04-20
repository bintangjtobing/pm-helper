@php
    $flat = [];
    foreach ($results as $category => $items) {
        foreach ($items as $item) {
            $flat[] = $item;
        }
    }
@endphp

<div x-data="{
        open: @entangle('open'),
        selectedIndex: 0,
        flatUrls: @js(array_column($flat, 'url')),
        isMac: /Mac|iPod|iPhone|iPad/.test(navigator.platform),

        init() {
            window.addEventListener('keydown', (e) => {
                const cmd = e.metaKey || e.ctrlKey;
                if (cmd && e.key === 'k') {
                    e.preventDefault();
                    this.toggle();
                }
                if (e.key === 'Escape' && this.open) {
                    e.preventDefault();
                    this.close();
                }
            });
        },

        toggle() {
            this.open = !this.open;
            this.selectedIndex = 0;
            if (this.open) {
                this.$nextTick(() => this.$refs.searchInput?.focus());
            }
        },
        close() {
            this.open = false;
            this.selectedIndex = 0;
            $wire.close();
        },
        moveDown() {
            if (this.selectedIndex < this.flatUrls.length - 1) this.selectedIndex++;
            this.$nextTick(() => this.scrollSelectedIntoView());
        },
        moveUp() {
            if (this.selectedIndex > 0) this.selectedIndex--;
            this.$nextTick(() => this.scrollSelectedIntoView());
        },
        scrollSelectedIntoView() {
            const el = document.querySelector('[data-cmd-idx=\\'' + this.selectedIndex + '\\']');
            el?.scrollIntoView({ block: 'nearest' });
        },
        selectCurrent() {
            const url = this.flatUrls[this.selectedIndex];
            if (url) window.location.href = url;
        }
    }"
    x-init="$watch('open', v => { if (!v) $wire.close() })"
    wire:ignore.self>

    {{-- Trigger button (fixed near topbar-right) --}}
    <button type="button"
            @click="toggle()"
            class="fixed top-3 right-5 z-40 group inline-flex items-center gap-2 pl-2.5 pr-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white/90 dark:bg-gray-800/90 backdrop-blur hover:bg-white dark:hover:bg-gray-800 hover:border-indigo-400 dark:hover:border-indigo-500 shadow-sm transition-all text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
            aria-label="Search (Cmd+K)"
            title="Search anything (⌘K)">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <span class="hidden sm:inline text-[12.5px] font-medium">Search</span>
        <kbd class="hidden sm:inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 text-[10px] font-mono text-gray-500 dark:text-gray-400">
            <span x-text="isMac ? '⌘' : 'Ctrl'"></span>K
        </kbd>
    </button>

    {{-- Backdrop + Modal --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak
             class="fixed inset-0 z-[60] flex items-start justify-center pt-20 px-4 bg-slate-900/70 backdrop-blur-sm"
             @click.self="close()"
             @keydown.escape.window="close()"
             x-transition.opacity.duration.150ms>

            {{-- Dialog (stop propagation so clicks inside don't close) --}}
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
                           wire:model.debounce.200ms="query"
                           @keydown.down.prevent="moveDown()"
                           @keydown.up.prevent="moveUp()"
                           @keydown.enter.prevent="selectCurrent()"
                           type="text"
                           placeholder="Search menus, tickets, projects, people, goals…"
                           class="flex-1 bg-transparent text-[14px] text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none border-0 p-0" />
                    <kbd class="hidden sm:inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-[10px] font-mono text-gray-500 dark:text-gray-400 shrink-0">Esc</kbd>
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
                                       data-cmd-idx="{{ $globalIdx }}"
                                       :class="selectedIndex === {{ $globalIdx }} ? 'bg-indigo-50 dark:bg-indigo-500/15 text-gray-900 dark:text-gray-100' : 'hover:bg-gray-50 dark:hover:bg-gray-800/60 text-gray-700 dark:text-gray-300'"
                                       @mouseenter="selectedIndex = {{ $globalIdx }}"
                                       class="flex items-center gap-3 px-4 py-2 transition-colors">
                                        <span class="shrink-0 w-7 h-7 rounded-md flex items-center justify-center bg-gray-100 dark:bg-gray-800">
                                            @svg($item['icon'], 'w-4 h-4 text-gray-500 dark:text-gray-400')
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-[13px] font-medium truncate">{{ $item['label'] }}</div>
                                            @if(! empty($item['group']))
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $item['group'] }}</div>
                                            @endif
                                        </div>
                                        <span :class="selectedIndex === {{ $globalIdx }} ? 'opacity-100' : 'opacity-0'"
                                              class="text-[10px] font-mono text-gray-400 dark:text-gray-500 transition-opacity shrink-0">
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
    </template>
</div>
