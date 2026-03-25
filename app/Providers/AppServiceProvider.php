<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Di AppServiceProvider.php dalam method boot()
        Filament::registerRenderHook(
            'head.end',
            fn (): string => '<style>
                /* Dark mode: backgrounds */
                html.dark { background-color: #111827 !important; }
                html.dark body { background-color: #111827 !important; }
                html.dark .filament-main { background-color: #111827 !important; }
                html.dark .filament-main-content { background-color: #111827 !important; }

                /* Dark mode: text colors */
                html.dark .text-gray-900 { color: #f9fafb !important; }
                html.dark .text-gray-800 { color: #e5e7eb !important; }
                html.dark .text-gray-700 { color: #d1d5db !important; }
                html.dark .text-gray-600 { color: #9ca3af !important; }

                /* Dark mode: card & surface backgrounds */
                html.dark .bg-white { background-color: #1f2937 !important; }
                html.dark .bg-gray-50 { background-color: #1f2937 !important; }
                html.dark .bg-gray-100 { background-color: #111827 !important; }
                html.dark .bg-gray-200 { background-color: #374151 !important; }
                html.dark .bg-gray-300 { background-color: #4b5563 !important; }

                /* Dark mode: colored backgrounds (soften for dark) */
                html.dark .bg-blue-50 { background-color: rgba(59, 130, 246, 0.1) !important; }
                html.dark .bg-green-50 { background-color: rgba(16, 185, 129, 0.1) !important; }
                html.dark .bg-red-50 { background-color: rgba(239, 68, 68, 0.1) !important; }
                html.dark .bg-yellow-50 { background-color: rgba(245, 158, 11, 0.1) !important; }
                html.dark .bg-purple-50 { background-color: rgba(139, 92, 246, 0.1) !important; }
                html.dark .bg-blue-100 { background-color: rgba(59, 130, 246, 0.2) !important; }
                html.dark .bg-green-100 { background-color: rgba(16, 185, 129, 0.2) !important; }
                html.dark .bg-red-100 { background-color: rgba(239, 68, 68, 0.2) !important; }
                html.dark .bg-yellow-100 { background-color: rgba(245, 158, 11, 0.2) !important; }
                html.dark .bg-purple-100 { background-color: rgba(139, 92, 246, 0.2) !important; }

                /* Dark mode: borders */
                html.dark .border-gray-100 { border-color: #374151 !important; }
                html.dark .border-gray-200 { border-color: #374151 !important; }
                html.dark .border-gray-300 { border-color: #4b5563 !important; }
                html.dark .border-blue-200 { border-color: rgba(59, 130, 246, 0.3) !important; }
                html.dark .border-yellow-200 { border-color: rgba(245, 158, 11, 0.3) !important; }
                html.dark .border-yellow-400 { border-color: rgba(245, 158, 11, 0.5) !important; }
                html.dark .border-red-200 { border-color: rgba(239, 68, 68, 0.3) !important; }

                /* Dark mode: hover states */
                html.dark .hover\:bg-gray-50:hover { background-color: #374151 !important; }
                html.dark .hover\:bg-gray-100:hover { background-color: #374151 !important; }
                html.dark .hover\:bg-blue-100:hover { background-color: rgba(59, 130, 246, 0.25) !important; }

                /* Dark mode: JSGantt chart overrides */
                html.dark .JSGantt,
                html.dark .gantt { background-color: #1f2937 !important; color: #e5e7eb !important; }
                html.dark .gchartcontainer { background-color: #1f2937 !important; }
                html.dark .gmajorheading { background-color: #374151 !important; color: #e5e7eb !important; border-color: #4b5563 !important; }
                html.dark .gminorheading { background-color: #1f2937 !important; color: #d1d5db !important; border-color: #4b5563 !important; }
                html.dark .gheadcell { border-color: #4b5563 !important; }
                html.dark .gname { border-color: #4b5563 !important; color: #e5e7eb !important; }
                html.dark .gplancontain { background-color: #1f2937 !important; border-color: #4b5563 !important; }
                html.dark .gtaskcellwkend { background-color: #111827 !important; }
                html.dark .gtaskcell { border-color: #374151 !important; }
                html.dark .glinediv { border-color: #4b5563 !important; }
                html.dark .gtaskheading,
                html.dark .gresource,
                html.dark .gduration,
                html.dark .gpccomplete { color: #d1d5db !important; border-color: #4b5563 !important; }
                html.dark .gstartdate,
                html.dark .genddate { color: #9ca3af !important; border-color: #4b5563 !important; }
                html.dark .gitemgroup td { background-color: #374151 !important; color: #e5e7eb !important; }
                html.dark .gitem td,
                html.dark .glineitem td { background-color: #1f2937 !important; color: #e5e7eb !important; }
                html.dark .gname a { color: #93c5fd !important; }
                html.dark .gformlabel { background-color: #374151 !important; color: #e5e7eb !important; border-color: #4b5563 !important; }
                html.dark .gcurrcell { background-color: rgba(99, 102, 241, 0.15) !important; }
                html.dark .rhscrpad { background-color: #1f2937 !important; }
                html.dark .ghead { background-color: #374151 !important; border-color: #4b5563 !important; }

                /* Dark mode: inline mention styles */
                html.dark .mention-tag { background-color: rgba(59, 130, 246, 0.2) !important; color: #93c5fd !important; border-color: rgba(59, 130, 246, 0.3) !important; }

                /* Dark mode: dialog/modal styles */
                html.dark .dialog-container { background-color: rgba(0, 0, 0, 0.6) !important; }
                html.dark .dialog { background-color: #1f2937 !important; border-color: #374151 !important; }
                html.dark .dialog-header { background-color: #374151 !important; color: #f9fafb !important; }
                html.dark .dialog-content { background-color: #1f2937 !important; }

                /* Dark mode: prose/content areas */
                html.dark .prose { color: #e5e7eb !important; }
                html.dark .prose blockquote { border-left-color: #4b5563 !important; }
                html.dark .prose pre { background-color: #111827 !important; }
                html.dark .prose code { background-color: #111827 !important; color: #f9fafb !important; }

                /* Dark mode: sortable ghost (kanban drag) */
                html.dark .sortable-ghost { background: linear-gradient(135deg, #374151, #1f2937) !important; }
            </style>'
        );
        // Configure application
        $this->configureApp();

        // Register custom Filament theme
        Filament::serving(function () {
            Filament::registerTheme(
                app(Vite::class)('resources/css/filament.scss'),
            );

            $appName = config('app.name');
            $appLogo = config('app.logo');
            $appLogoDark = config('app.logo_dark');

            if ($appLogo || $appLogoDark) {
                // Enhanced styles for dark mode logo support
                Filament::registerRenderHook(
                    'body.start',
                    fn (): string => '<style>
                        .filament-main-sidebar-brand {
                            display: flex;
                            align-items: center;
                            gap: 0.75rem;
                        }
                        .filament-main-sidebar-brand img {
                            height: 2rem;
                            width: auto;
                            transition: opacity 0.3s ease;
                        }

                        /* Dark mode specific styles */
                        @media (prefers-color-scheme: dark) {
                            .dark .filament-main-sidebar-brand img.light-logo {
                                display: none;
                            }
                            .dark .filament-main-sidebar-brand img.dark-logo {
                                display: block;
                            }
                        }

                        /* Light mode specific styles */
                        @media (prefers-color-scheme: light) {
                            .filament-main-sidebar-brand img.light-logo {
                                display: block;
                            }
                            .filament-main-sidebar-brand img.dark-logo {
                                display: none;
                            }
                        }

                        /* Manual dark mode toggle support */
                        .dark .filament-main-sidebar-brand img:not(.dark-logo) {
                            display: none;
                        }

                        .dark .filament-main-sidebar-brand img.dark-logo {
                            display: block !important;
                        }

                        /* Fallback filter for logos without dark variant */
                        .filament-main-sidebar-brand img.auto-invert {
                            filter: brightness(0) invert(1);
                        }

                        /* Logo loading states */
                        .filament-main-sidebar-brand img[src=""] {
                            display: none;
                        }

                        /* Smooth theme transition */
                        .filament-main-sidebar-brand * {
                            transition: all 0.2s ease-in-out;
                        }
                    </style>'
                );
            }
        });

        // Register tippy styles
        Filament::registerStyles([
            'https://unpkg.com/tippy.js@6/dist/tippy.css',
        ]);

        // Register scripts
        try {
            Filament::registerScripts([
                app(Vite::class)('resources/js/filament.js'),
            ]);
        } catch (\Exception $e) {
            // Manifest not built yet!
        }

        // Add custom meta (favicon) - support for dark mode favicon too
        $favicon = config('app.logo_dark') ?: config('app.logo');
        Filament::pushMeta([
            new HtmlString('<link rel="icon" type="image/x-icon" href="' . $favicon . '">'),
            new HtmlString('<link rel="icon" type="image/x-icon" href="' . $favicon . '" media="(prefers-color-scheme: dark)">'),
        ]);

        // Register navigation groups
        Filament::registerNavigationGroups([
            __('Management'),
            __('Referential'),
            __('Security'),
            __('Settings'),
        ]);

        // Force HTTPS over HTTP
        if (env('APP_FORCE_HTTPS') ?? false) {
            URL::forceScheme('https');
        }

        Blade::component('user-avatar', \App\View\Components\UserAvatar::class);

        // Override Filament config for user avatar (for Filament v2)
        config(['filament.user.avatar' => function ($user) {
            return $user->avatar_url ?: null;
        }]);
    }

    private function configureApp(): void
    {
        try {
            $settings = app(GeneralSettings::class);
            Config::set('app.locale', $settings->site_language ?? config('app.fallback_locale'));
            Config::set('app.name', $settings->site_name ?? env('APP_NAME'));
            Config::set('filament.brand', $settings->site_name ?? env('APP_NAME'));

            // Configure light mode logo
            Config::set(
                'app.logo',
                $settings->site_logo ? asset('storage/' . $settings->site_logo) : (env('APP_LOGO') ?: asset('favicon.ico'))
            );

            // Configure dark mode logo
            $darkLogo = null;
            if (isset($settings->site_logo_dark) && $settings->site_logo_dark) {
                $darkLogo = asset('storage/' . $settings->site_logo_dark);
            } elseif (env('APP_LOGO_DARK')) {
                $darkLogo = env('APP_LOGO_DARK');
            }
            Config::set('app.logo_dark', $darkLogo);

            Config::set('filament-breezy.enable_registration', $settings->enable_registration ?? false);
            Config::set('filament-socialite.registration', $settings->enable_registration ?? false);
            Config::set('filament-socialite.enabled', $settings->enable_social_login ?? false);
            Config::set('system.login_form.is_enabled', $settings->enable_login_form ?? false);
            Config::set('services.oidc.is_enabled', $settings->enable_oidc_login ?? false);
        } catch (QueryException $e) {
            // Error: No database configured yet
        }
    }
}