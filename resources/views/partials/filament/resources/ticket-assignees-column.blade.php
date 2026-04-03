@php
    $record = $getRecord();
    $owner = $record->owner;
    $responsible = $record->responsible;
    $avatarUrl = fn($user) => $user->getAttributes()['avatar_url']
        ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
@endphp
<div class="flex flex-col gap-1.5">
    @if($owner)
    <div class="flex items-center gap-2">
        <img src="{{ $avatarUrl($owner) }}" alt="{{ $owner->name }}" class="object-cover w-6 h-6 rounded-full shrink-0" loading="lazy" />
        <div class="min-w-0">
            <div class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">{{ $owner->name }}</div>
            <div class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('Owner') }}</div>
        </div>
    </div>
    @endif
    @if($responsible && (!$owner || $responsible->id !== $owner->id))
    <div class="flex items-center gap-2">
        <img src="{{ $avatarUrl($responsible) }}" alt="{{ $responsible->name }}" class="object-cover w-6 h-6 rounded-full shrink-0" loading="lazy" />
        <div class="min-w-0">
            <div class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">{{ $responsible->name }}</div>
            <div class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('Responsible') }}</div>
        </div>
    </div>
    @endif
</div>
