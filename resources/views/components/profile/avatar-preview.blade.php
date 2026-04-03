@php
    $user = auth()->user();
    $rawAvatarUrl = $user->getAttributes()['avatar_url'] ?? null;
@endphp
<div class="p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col items-center space-y-4">
        <div class="w-24 h-24 overflow-hidden border-2 rounded-full border-primary-500">
            @if($rawAvatarUrl)
            <img src="{{ $rawAvatarUrl }}" alt="{{ $user->name }}"
                class="object-cover w-full h-full" />
            @else
            <div class="flex items-center justify-center w-full h-full bg-gray-200 dark:bg-gray-700">
                <span class="text-gray-500 dark:text-gray-400">{{ __('No avatar') }}</span>
            </div>
            @endif
        </div>

        <div class="text-center">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Current Profile Picture') }}</h3>
        </div>

        @if($rawAvatarUrl)
        <button type="button" wire:click="removeAvatar" class="text-sm text-red-600 hover:text-red-800 hover:underline dark:text-red-400">
            {{ __('Remove Current Picture') }}
        </button>
        @endif
    </div>
</div>
