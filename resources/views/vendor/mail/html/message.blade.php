<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ config('app.name') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    <div class="notification-header">
        <div class="notification-badge">
            NOTIFICATION
        </div>
    </div>

    <div class="notification-box">
        <div class="decorative-line"></div>

        {!! $slot !!}
    </div>

    {{-- Subcopy --}}
    @isset($subcopy)
    <x-slot:subcopy>
        <x-mail::subcopy>
            <div class="subcopy">
                <div class="info-header">
                    <div class="info-icon"></div>
                    <span class="info-title">Important Information</span>
                </div>
                {{ $subcopy }}
            </div>
        </x-mail::subcopy>
    </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
