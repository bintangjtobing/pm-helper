<div class="space-y-4">
    {{-- Summary Bar --}}
    @if($summary['total'] > 0)
    <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('QA Progress') }}
                </span>
                <span class="text-xs text-gray-500">
                    {{ $summary['passed'] }}/{{ $summary['total'] }} {{ __('passed') }}
                </span>
            </div>
            <div class="w-full h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="flex h-2 rounded-full overflow-hidden">
                    @if($summary['passed'] > 0)
                    <div class="bg-green-500" style="width: {{ ($summary['passed'] / $summary['total']) * 100 }}%"></div>
                    @endif
                    @if($summary['failed'] > 0)
                    <div class="bg-red-500" style="width: {{ ($summary['failed'] / $summary['total']) * 100 }}%"></div>
                    @endif
                    @if($summary['pending'] > 0)
                    <div class="bg-gray-300 dark:bg-gray-600" style="width: {{ ($summary['pending'] / $summary['total']) * 100 }}%"></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                {{ $summary['passed'] }} {{ __('passed') }}
            </span>
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                {{ $summary['failed'] }} {{ __('failed') }}
            </span>
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                {{ $summary['pending'] }} {{ __('pending') }}
            </span>
        </div>
    </div>
    @endif

    {{-- Checklist Items --}}
    <div class="space-y-2">
        @forelse($checklists as $item)
        <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700
            {{ $item->status === 'passed' ? 'bg-green-50 dark:bg-green-900/10' : '' }}
            {{ $item->status === 'failed' ? 'bg-red-50 dark:bg-red-900/10' : '' }}
            {{ $item->status === 'pending' ? 'bg-white dark:bg-gray-800' : '' }}">

            {{-- Status Buttons --}}
            <div class="flex flex-col gap-1 mt-0.5">
                <button wire:click="setStatus({{ $item->id }}, 'passed')"
                    class="p-1 rounded transition-colors {{ $item->status === 'passed' ? 'text-green-600 bg-green-100 dark:bg-green-900/30' : 'text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20' }}"
                    title="{{ __('Pass') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <button wire:click="setStatus({{ $item->id }}, 'failed')"
                    class="p-1 rounded transition-colors {{ $item->status === 'failed' ? 'text-red-600 bg-red-100 dark:bg-red-900/30' : 'text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20' }}"
                    title="{{ __('Fail') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 {{ $item->status === 'passed' ? 'line-through opacity-60' : '' }}">
                    {{ $item->description }}
                </p>

                {{-- Notes --}}
                @if($editingItemId === $item->id)
                <div class="mt-2">
                    <textarea wire:model.defer="editingNotes" rows="2"
                        class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="{{ __('Add notes...') }}"></textarea>
                    <div class="flex gap-2 mt-1">
                        <button wire:click="saveNotes" class="px-2 py-1 text-xs text-white bg-primary-500 rounded hover:bg-primary-600">
                            {{ __('Save') }}
                        </button>
                        <button wire:click="cancelEditNotes" class="px-2 py-1 text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
                @elseif($item->notes)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300"
                   wire:click="startEditNotes({{ $item->id }})">
                    {{ $item->notes }}
                </p>
                @else
                <button wire:click="startEditNotes({{ $item->id }})" class="mt-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    + {{ __('Add notes') }}
                </button>
                @endif

                {{-- Meta --}}
                <div class="flex items-center gap-2 mt-1 text-xs text-gray-400">
                    <span>{{ $item->user->name }}</span>
                    <span>&middot;</span>
                    <span>{{ $item->created_at->diffForHumans() }}</span>
                </div>
            </div>

            {{-- Delete --}}
            <button wire:click="deleteItem({{ $item->id }})"
                wire:confirm="{{ __('Are you sure you want to delete this checklist item?') }}"
                class="p-1 text-gray-400 hover:text-red-500 transition-colors"
                title="{{ __('Delete') }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        @empty
        <div class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            {{ __('No QA checklist items yet. Add items below to start testing.') }}
        </div>
        @endforelse
    </div>

    {{-- Add New Item --}}
    <form wire:submit.prevent="addItem" class="flex items-center gap-2">
        <input type="text" wire:model.defer="newItemDescription"
            class="flex-1 text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500"
            placeholder="{{ __('Add a QA checklist item...') }}"
            required>
        <button type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded hover:bg-primary-600 transition-colors whitespace-nowrap">
            {{ __('Add Item') }}
        </button>
    </form>
</div>
