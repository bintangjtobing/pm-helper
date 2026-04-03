<div class="flex items-center gap-3">
    <img src="{{ $getRecord()->avatar_url }}"
         alt="{{ $getRecord()->name }}"
         class="object-cover w-9 h-9 rounded-full shrink-0"
         loading="lazy" />
    <div class="min-w-0">
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
            {{ $getRecord()->name }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
            {{ '@' . $getRecord()->username }} &middot; {{ $getRecord()->email }}
        </div>
    </div>
</div>
