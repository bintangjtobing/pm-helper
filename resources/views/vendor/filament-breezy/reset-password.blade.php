@php
    $quote = \App\Models\MotivationalQuote::random();

    $backgrounds = [];
    $authTheme = 'dark';
    try {
        $settings = app(\App\Settings\GeneralSettings::class);
        $backgrounds = $settings->login_backgrounds ?? [];
        $authTheme = $settings->default_auth_theme ?? 'dark';
    } catch (\Exception $e) {}
    $bgImage = !empty($backgrounds) ? asset('storage/' . $backgrounds[array_rand($backgrounds)]) : null;

    $appLogo = config('app.logo');
    $appLogoDark = config('app.logo_dark');
    $appName = config('app.name');
@endphp

<div class="flex items-center justify-center min-h-screen p-3 sm:p-4 lg:p-6 filament-breezy-auth-component">
    {{-- Force auth theme --}}
    <script>
        (function() {
            var theme = '{{ $authTheme }}';
            if (theme === 'dark') { document.documentElement.classList.add('dark'); }
            else if (theme === 'light') { document.documentElement.classList.remove('dark'); }
        })();
    </script>
    <style>
        html, html body.filament-body { background-color: #f3f4f6 !important; }
        html.dark, html.dark body.filament-body { background-color: #030712 !important; }
        .login-card .filament-forms-component-container { gap: 0.75rem !important; }
        .login-card .filament-forms-field-wrapper { padding: 0 !important; }
    </style>

    {{-- Card Container --}}
    <div class="login-card flex w-full overflow-hidden bg-white border border-gray-200 shadow-2xl rounded-2xl dark:bg-gray-900 dark:border-gray-800"
         style="max-width: 1100px; min-height: min(80vh, 640px);">

        {{-- Left Panel: Image + Quote --}}
        <div class="relative flex-shrink-0 hidden overflow-hidden lg:flex" style="width: 44%;">
            @if($bgImage)
                <img src="{{ $bgImage }}" alt="" class="absolute inset-0 object-cover w-full h-full" loading="eager">
            @else
                <div class="absolute inset-0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);"></div>
            @endif

            <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.25) 45%, rgba(0,0,0,0.45) 100%);"></div>

            <div class="relative z-10 flex flex-col justify-between w-full h-full p-8 xl:p-10">
                <div>
                    @if($appLogoDark)
                        <img src="{{ $appLogoDark }}" alt="{{ $appName }}" class="w-auto h-10">
                    @elseif($appLogo && !str_ends_with($appLogo, 'favicon.ico'))
                        <img src="{{ $appLogo }}" alt="{{ $appName }}" class="w-auto h-10" style="filter: brightness(0) invert(1);">
                    @else
                        <span class="text-xl font-bold tracking-tight text-white">{{ $appName }}</span>
                    @endif
                </div>

                @if($quote)
                <div>
                    <p class="text-lg font-light leading-relaxed xl:text-xl" style="color: rgba(255,255,255,0.9); text-shadow: 0 1px 3px rgba(0,0,0,0.4);">
                        "{{ $quote->quote }}"
                    </p>
                    <p class="mt-3 text-sm font-medium" style="color: rgba(255,255,255,0.5);">
                        &mdash; {{ $quote->author }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- Right Panel: Reset Password Form --}}
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-8 sm:px-10 lg:px-14 xl:px-16">
            <div class="w-full max-w-sm">

                <div class="flex justify-center mb-6 lg:hidden">
                    <x-filament::brand />
                </div>

                <div class="mb-6">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ __('filament-breezy::default.reset_password.heading') }}
                    </h1>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Enter your email and we\'ll send you a reset link.') }}
                    </p>
                </div>

                @unless($hasBeenSent)
                <form wire:submit.prevent="submit">
                    <div class="space-y-4">
                        {{ $this->form }}
                    </div>

                    <div class="mt-5">
                        <x-filament::button type="submit" class="w-full">
                            {{ __('filament-breezy::default.reset_password.submit.label') }}
                        </x-filament::button>
                    </div>
                </form>
                @else
                <div class="p-4 text-sm font-medium text-center rounded-lg text-success-600 bg-success-50 dark:bg-success-900/20">
                    {{ __('filament-breezy::default.reset_password.notification_success') }}
                </div>
                @endunless

                <div class="mt-6 text-center">
                    <a class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400"
                       href="{{ route('filament.auth.login') }}">
                        &larr; {{ __('filament::login.heading') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @livewire('notifications')
</div>
