@php
    $record = $getRecord();
    $users = $record->users;
    $shown = $users->take(4);
    $remaining = $users->count() - 4;
@endphp
<div class="flex items-center">
    <div class="flex -space-x-2">
        @foreach($shown as $user)
        @php
            $avatar = $user->getAttributes()['avatar_url']
                ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');
        @endphp
        <img src="{{ $avatar }}"
             alt="{{ $user->name }}"
             title="{{ $user->name }}"
             class="object-cover w-7 h-7 rounded-full ring-2 ring-white dark:ring-gray-800"
             loading="lazy" />
        @endforeach
        @if($remaining > 0)
        <div class="flex items-center justify-center w-7 h-7 text-[10px] font-medium text-gray-600 bg-gray-100 rounded-full ring-2 ring-white dark:ring-gray-800 dark:bg-gray-700 dark:text-gray-400">
            +{{ $remaining }}
        </div>
        @endif
    </div>
</div>
