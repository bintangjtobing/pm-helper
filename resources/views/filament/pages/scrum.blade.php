<x-filament::page>

    @if($project->currentSprint)

        <div class="mx-auto w-full" wire:ignore>
            <details class="w-full bg-white open:bg-gray-200 duration-300">
                <summary
                    class="relative w-full bg-inherit px-5 py-3 text-base cursor-pointer text-gray-500">
                    {{ __('Filters') }}
                </summary>
                <div class="bg-white px-5 py-3">
                    <form>
                        {{ $this->form }}
                    </form>
                </div>
            </details>
        </div>

        {{-- Column Visibility Controls --}}
        <div class="flex flex-wrap items-center justify-end gap-2 mb-3">
            <div class="relative" x-data="kanbanColumnToggle({
                projectId: {{ $this->project->id }},
                statuses: @js($this->getStatuses()->map(fn($s) => ['id' => $s['id'], 'title' => $s['title'], 'color' => $s['color']])->values())
            })" x-init="init()">
                <button type="button" @click="open = !open" @click.outside="open = false"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                    <span x-text="`{{ __('Columns') }} (${visibleCount}/${statuses.length})`"></span>
                </button>

                <div x-show="open" x-cloak x-transition
                    class="absolute right-0 z-30 w-64 mt-2 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800 dark:ring-gray-700">
                    <div class="p-2">
                        <div class="flex items-center justify-between px-2 py-1 mb-1 border-b dark:border-gray-700">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                {{ __('Show columns') }}
                            </span>
                            <div class="flex gap-2">
                                <button type="button" @click="showAll()"
                                    class="text-xs text-primary-500 hover:underline">{{ __('All') }}</button>
                                <button type="button" @click="hideAll()"
                                    class="text-xs text-gray-500 hover:underline">{{ __('None') }}</button>
                            </div>
                        </div>
                        <template x-for="status in statuses" :key="status.id">
                            <label
                                class="flex items-center gap-2 px-2 py-1.5 text-sm rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">
                                <input type="checkbox" :checked="isVisible(status.id)"
                                    @change="toggle(status.id)"
                                    class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <span class="inline-block w-2.5 h-2.5 rounded-full"
                                    :style="`background-color: ${status.color}`"></span>
                                <span x-text="status.title"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="kanban-container">

            @foreach($this->getStatuses() as $status)
                @include('partials.kanban.status')
            @endforeach

        </div>

        @push('scripts')
            <script src="{{ asset('js/Sortable.js') }}"></script>
            <script>
                // Column visibility toggle (Alpine component) — shared logic with kanban page
                if (!window.kanbanColumnToggle) {
                    window.kanbanColumnToggle = function(config) {
                        return {
                            open: false,
                            projectId: config.projectId,
                            statuses: config.statuses || [],
                            hidden: [],
                            storageKey: 'kanban_hidden_cols_project_' + config.projectId,

                            init() {
                                this.loadHidden();
                                this.applyHidden();
                                if (window.Livewire) {
                                    window.Livewire.hook('message.processed', () => this.applyHidden());
                                }
                            },
                            loadHidden() {
                                try {
                                    const raw = localStorage.getItem(this.storageKey);
                                    this.hidden = raw ? JSON.parse(raw) : [];
                                    if (!Array.isArray(this.hidden)) this.hidden = [];
                                } catch (e) { this.hidden = []; }
                            },
                            saveHidden() {
                                try { localStorage.setItem(this.storageKey, JSON.stringify(this.hidden)); } catch (e) {}
                            },
                            isVisible(statusId) { return !this.hidden.includes(statusId); },
                            get visibleCount() {
                                return this.statuses.length - this.hidden.filter(id =>
                                    this.statuses.some(s => s.id === id)
                                ).length;
                            },
                            toggle(statusId) {
                                const idx = this.hidden.indexOf(statusId);
                                if (idx === -1) this.hidden.push(statusId);
                                else this.hidden.splice(idx, 1);
                                this.saveHidden();
                                this.applyHidden();
                            },
                            showAll() { this.hidden = []; this.saveHidden(); this.applyHidden(); },
                            hideAll() { this.hidden = this.statuses.map(s => s.id); this.saveHidden(); this.applyHidden(); },
                            applyHidden() {
                                document.querySelectorAll('.kanban-statuses[data-status-id]').forEach(el => {
                                    const id = parseInt(el.dataset.statusId, 10);
                                    el.style.display = this.hidden.includes(id) ? 'none' : '';
                                });
                            },
                        };
                    };
                }

                (function() {
                    const scrumProjectId = {{ $this->project->id }};
                    const scrumCurrentUserId = {{ auth()->id() }};
                    let scrumChannelSubscribed = false;
                    let scrumRefreshPending = false;

                    function initSortable() {
                        document.querySelectorAll('[id^="status-records-"]').forEach(function(container) {
                            if (container._sortableInstance) {
                                try { container._sortableInstance.destroy(); } catch (e) {}
                            }
                            const statusId = parseInt(container.dataset.status || container.id.replace('status-records-', ''), 10);
                            container._sortableInstance = Sortable.create(container, {
                                group: {
                                    name: 'status-' + statusId,
                                    pull: true,
                                    put: true,
                                },
                                handle: '.handle',
                                animation: 100,
                                onEnd: function(evt) {
                                    Livewire.emit('recordUpdated',
                                        +evt.clone.dataset.id,
                                        +evt.newIndex,
                                        +(evt.to.dataset.status || evt.to.id.replace('status-records-', ''))
                                    );
                                },
                            });
                        });
                    }

                    function subscribeChannel() {
                        if (scrumChannelSubscribed || !window.Echo) return;
                        try {
                            window.Echo.private('project.' + scrumProjectId + '.kanban')
                                .listen('.ticket.moved', function(e) {
                                    if (parseInt(e.moved_by_user_id, 10) === scrumCurrentUserId) return;
                                    if (scrumRefreshPending) return;
                                    scrumRefreshPending = true;
                                    requestAnimationFrame(function() {
                                        scrumRefreshPending = false;
                                        const el = document.querySelector('.kanban-container')?.closest('[wire\\:id]');
                                        if (!el) return;
                                        const comp = window.Livewire.find(el.getAttribute('wire:id'));
                                        if (comp) comp.call('filter');
                                    });
                                });
                            scrumChannelSubscribed = true;
                        } catch (err) {
                            console.warn('[scrum] failed to subscribe to live updates', err);
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(function() {
                            initSortable();
                            subscribeChannel();
                        }, 500);
                    });

                    if (window.Livewire) {
                        window.Livewire.hook('message.processed', function() {
                            initSortable();
                        });
                    }
                })();
            </script>
        @endpush
    @else
        <div class="w-full flex flex-col">
            <span class="text-gray-500 text-lg font-medium">
                {{ __('No active sprint for this project!') }}
            </span>
            @if(auth()->user()->can('update', $project))
                <span class="text-gray-500 text-sm">
                    {{ __("Click the below button to manage project's sprints") }}
                </span>
                <a href="{{ route('filament.resources.projects.view', $project) }}"
                   class="px-3 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded mt-3 w-fit">
                    {{ __('Manage sprints') }}
                </a>
            @else
                <span class="text-gray-500 text-sm">
                    {{ __("If you think a sprint should be started, please contact an administrator") }}
                </span>
            @endif
        </div>
    @endif

</x-filament::page>
