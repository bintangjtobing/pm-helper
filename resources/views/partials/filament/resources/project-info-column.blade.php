@php
    $record = $getRecord();
    $cover = $record->getFirstMediaUrl('cover');
    $ownerAvatar = $record->owner
        ? ($record->owner->getAttributes()['avatar_url'] ?? ('https://ui-avatars.com/api/?name=' . urlencode($record->owner->name) . '&size=64&background=' . substr(md5($record->owner->id), 0, 6) . '&color=ffffff'))
        : null;
@endphp
<div class="flex items-center gap-3">
    @if($cover)
    <img src="{{ $cover }}" alt="{{ $record->name }}" class="object-cover w-10 h-10 rounded-lg shrink-0" loading="lazy" />
    @else
    <div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-lg shrink-0"
         style="background: linear-gradient(135deg, #{{ substr(md5($record->name), 0, 6) }}, #{{ substr(md5($record->name . 'x'), 0, 6) }})">
        {{ strtoupper(substr($record->name, 0, 2)) }}
    </div>
    @endif
    <div class="min-w-0">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $record->name }}</span>
            <span class="px-1.5 py-0.5 text-[10px] font-semibold uppercase rounded {{ $record->type === 'scrum' ? 'bg-amber-500/10 text-amber-500' : 'bg-blue-500/10 text-blue-500' }}">
                {{ $record->type }}
            </span>
        </div>
        <div class="flex items-center gap-1.5 mt-0.5">
            @if($ownerAvatar)
            <img src="{{ $ownerAvatar }}" class="w-4 h-4 rounded-full" loading="lazy" />
            @endif
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $record->owner?->name }}</span>
            <span class="text-xs font-mono text-gray-400 dark:text-gray-500">&middot; {{ $record->ticket_prefix }}</span>
        </div>
    </div>
</div>
