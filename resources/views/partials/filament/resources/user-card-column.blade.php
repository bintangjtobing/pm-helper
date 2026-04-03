@php
    $record = $getRecord();
    $roles = $record->roles->pluck('name')->toArray();
    $roleColors = [
        'Super Admin' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'Project Manager' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'Developer' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'QA / Tester' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'DevOps' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'Stakeholder' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    ];
@endphp
<div class="flex flex-col items-center w-full p-2 text-center">
    <img src="{{ $record->avatar_url }}"
         alt="{{ $record->name }}"
         class="object-cover w-16 h-16 rounded-full"
         loading="lazy" />

    <div class="mt-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
        {{ $record->name }}
    </div>

    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
        {{ '@' . $record->username }}
    </div>

    <div class="mt-1 text-xs text-gray-400 dark:text-gray-500 truncate max-w-full">
        {{ $record->email }}
    </div>

    <div class="flex flex-wrap justify-center gap-1 mt-2">
        @foreach($roles as $role)
        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $roleColors[$role] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
            {{ $role }}
        </span>
        @endforeach
    </div>

    <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
        {{ __('Joined') }} {{ $record->created_at->format('M d, Y') }}
    </div>
</div>
