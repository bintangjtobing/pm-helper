@php
    $quote = \App\Models\MotivationalQuote::random();

    // Get settings
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

<div class="flex items-center justify-center min-h-screen p-3 sm:p-4 lg:p-6 filament-breezy-auth-component filament-login-page">
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

    {{-- Card Container - larger --}}
    <div class="login-card flex w-full overflow-hidden bg-white border border-gray-200 shadow-2xl rounded-2xl dark:bg-gray-900 dark:border-gray-800"
         style="max-width: 1100px; min-height: min(80vh, 640px);">

        {{-- Left Panel: Image + Quote --}}
        <div class="relative flex-shrink-0 hidden overflow-hidden lg:flex" style="width: 44%;">
            @if($bgImage)
                <img src="{{ $bgImage }}" alt="" class="absolute inset-0 object-cover w-full h-full" loading="eager">
            @else
                <div class="absolute inset-0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);"></div>
            @endif

            {{-- Dark gradient overlay --}}
            <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.25) 45%, rgba(0,0,0,0.45) 100%);"></div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-between w-full h-full p-8 xl:p-10">
                {{-- Logo (white version for dark overlay) --}}
                <div>
                    @if($appLogoDark)
                        <img src="{{ $appLogoDark }}" alt="{{ $appName }}" class="w-auto h-10">
                    @elseif($appLogo && !str_ends_with($appLogo, 'favicon.ico'))
                        <img src="{{ $appLogo }}" alt="{{ $appName }}" class="w-auto h-10" style="filter: brightness(0) invert(1);">
                    @else
                        <span class="text-xl font-bold tracking-tight text-white">{{ $appName }}</span>
                    @endif
                </div>

                {{-- Quote --}}
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

        {{-- Right Panel: Login Form --}}
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-8 sm:px-10 lg:px-14 xl:px-16">
            <div class="w-full max-w-sm">

                {{-- Mobile Logo --}}
                <div class="flex justify-center mb-6 lg:hidden">
                    <x-filament::brand />
                </div>

                {{-- Heading --}}
                <div class="mb-6">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ __('filament::login.heading') }}
                    </h1>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Welcome back! Please sign in to continue.') }}
                    </p>
                </div>

                {{-- OIDC Error --}}
                @if(session()->has('oidc_error'))
                <div class="p-3 mb-4 text-sm rounded-lg text-red-800 bg-red-50 dark:bg-red-900/20 dark:text-red-400" role="alert">
                    <span class="font-medium">{{ __('OIDC Connect error') }}</span> {{ __('Invalid account!') }}
                </div>
                @endif

                {{-- Login Form --}}
                @if(config('system.login_form.is_enabled'))
                <form wire:submit.prevent="authenticate">
                    <div class="space-y-4">
                        {{ $this->form }}
                    </div>

                    <div class="mt-5">
                        <x-filament::button type="submit" class="w-full">
                            {{ __('filament::login.buttons.submit.label') }}
                        </x-filament::button>
                    </div>

                    <div class="mt-3 text-center">
                        <a class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400"
                           href="{{ route(config('filament-breezy.route_group_prefix').'password.request') }}">
                            {{ __('filament-breezy::default.login.forgot_password_link') }}
                        </a>
                    </div>
                </form>
                @endif

                {{-- Divider --}}
                @if(config('system.login_form.is_enabled') && (config('services.oidc.is_enabled') || config('filament-socialite.enabled')))
                <div class="relative mt-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-3 text-gray-400 uppercase bg-white dark:bg-gray-900">{{ __('Or continue with') }}</span>
                    </div>
                </div>
                @endif

                {{-- OIDC Button --}}
                @if(config('services.oidc.is_enabled'))
                <div class="mt-5">
                    <x-filament::button color="secondary" class="w-full" tag="a" :href="route('oidc.redirect')">
                        <div class="flex items-center justify-center w-full gap-2">
                            <x-heroicon-o-login class="w-5 h-5" />
                            {{ __('OIDC Connect') }}
                        </div>
                    </x-filament::button>
                </div>
                @endif

                {{-- Social Login --}}
                @if(config('filament-socialite.enabled'))
                <div class="mt-5">
                    <x-filament-socialite::buttons />
                </div>
                @endif
            </div>
        </div>
    </div>

    {{ $this->modal }}
    @livewire('notifications')

    @php $changelogVersion = config('changelog.0.version', '1.0.0'); @endphp
    <div style="position: fixed; bottom: 16px; left: 50%; transform: translateX(-50%); z-index: 50;">
        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:rgba(17,24,39,0.55);color:#e5e7eb;border:1px solid rgba(255,255,255,0.08);border-radius:999px;font-size:11px;font-weight:500;backdrop-filter:blur(6px);">
            <span style="width:6px;height:6px;border-radius:50%;background:#34d399;"></span>
            PMHelper
            <span style="font-weight:600;color:#93c5fd;">v{{ $changelogVersion }}</span>
        </span>
    </div>
</div>
