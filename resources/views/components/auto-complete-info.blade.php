{{-- resources/views/components/auto-complete-info.blade.php --}}
<div class="p-4 mt-4 border border-blue-200 rounded-lg bg-blue-50">
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-medium text-blue-800">
                {{ __('Auto Complete Configuration') }}
            </h3>
            <div class="mt-2 text-sm text-blue-700">
                <p>
                    {{ __('When enabled, tickets that stay in') }}
                    <span class="font-semibold">"{{ $from_status }}"</span>
                    {{ __('status for more than') }}
                    <span class="font-semibold">{{ $days }} {{ __('days') }}</span>
                    {{ __('will automatically be moved to') }}
                    <span class="font-semibold">"{{ $to_status }}"</span>
                    {{ __('status.') }}
                </p>
                <p class="mt-1">
                    {{ __('This feature runs daily and helps prevent tickets from getting stuck in review.') }}
                </p>
            </div>
        </div>
    </div>
</div>