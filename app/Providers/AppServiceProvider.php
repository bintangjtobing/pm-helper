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
                html.dark .text-gray-800 { color: #f3f4f6 !important; }
                html.dark .text-gray-700 { color: #e5e7eb !important; }
                html.dark .text-gray-600 { color: #d1d5db !important; }
                html.dark .text-gray-500 { color: #d1d5db !important; }
                html.dark .text-gray-400 { color: #9ca3af !important; }

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
                html.dark .prose h1, html.dark .prose h2, html.dark .prose h3,
                html.dark .prose h4, html.dark .prose h5, html.dark .prose h6 { color: #f9fafb !important; }
                html.dark .prose p { color: #d1d5db !important; }
                html.dark .prose li { color: #d1d5db !important; }
                html.dark .prose strong { color: #f9fafb !important; }
                html.dark .prose a { color: #93c5fd !important; }
                html.dark .prose blockquote { border-left-color: #4b5563 !important; color: #9ca3af !important; }
                html.dark .prose pre { background-color: #111827 !important; }
                html.dark .prose code { background-color: #111827 !important; color: #f9fafb !important; }
                html.dark .prose ul ::marker { color: #9ca3af !important; }
                html.dark .prose ol ::marker { color: #9ca3af !important; }
                html.dark .prose hr { border-color: #374151 !important; }
                html.dark .prose thead th { color: #e5e7eb !important; border-color: #4b5563 !important; }
                html.dark .prose tbody td { border-color: #374151 !important; }

                /* Dark mode: colored text (for date badges, status badges) */
                html.dark .text-green-800 { color: #6ee7b7 !important; }
                html.dark .text-green-700 { color: #6ee7b7 !important; }
                html.dark .text-red-800 { color: #fca5a5 !important; }
                html.dark .text-red-700 { color: #fca5a5 !important; }
                html.dark .text-yellow-800 { color: #fcd34d !important; }
                html.dark .text-yellow-700 { color: #fcd34d !important; }
                html.dark .text-blue-800 { color: #93c5fd !important; }
                html.dark .text-blue-700 { color: #93c5fd !important; }
                html.dark .text-purple-800 { color: #c4b5fd !important; }
                html.dark .text-purple-700 { color: #c4b5fd !important; }
                html.dark .border-green-200 { border-color: rgba(16, 185, 129, 0.3) !important; }

                /* Dark mode: Kanban board */
                html.dark .status-header { background-color: #1f2937 !important; }
                html.dark .status-container { background-color: #1f2937 !important; }
                html.dark .kanban-record { background-color: #374151 !important; border-color: #4b5563 !important; }
                html.dark .kanban-record:hover { background-color: #4b5563 !important; }
                html.dark .kanban-record .record-title { color: #e5e7eb !important; }
                html.dark .kanban-record .record-subtitle { color: #9ca3af !important; }
                html.dark .kanban-record .code { color: #9ca3af !important; }
                html.dark .kanban-record .record-relations { border-color: rgba(75,85,99,0.5) !important; }
                html.dark .kanban-record .avatar { background-color: #4b5563 !important; }
                html.dark .create-record { color: #9ca3af !important; }
                html.dark .create-record:hover { background-color: #374151 !important; color: #d1d5db !important; }
                html.dark .sortable-ghost { background: linear-gradient(135deg, #374151, #1f2937) !important; }

                /* Dark mode: Kanban filter details */
                html.dark details { background-color: #1f2937 !important; }
                html.dark details[open] { background-color: #374151 !important; }
                html.dark details summary { color: #9ca3af !important; }

                /* Activity feed: force full width column */
                .filament-tables-text-column { max-width: none !important; }
                .filament-tables-column-wrapper { max-width: none !important; }
            </style>'
        );
        // Configure application
        $this->configureApp();

        // Sidebar blurred background from login images
        Filament::registerRenderHook(
            'head.end',
            function (): string {
                try {
                    $settings = app(GeneralSettings::class);
                    $backgrounds = $settings->login_backgrounds ?? [];
                    if (empty($backgrounds)) return '';
                    $bgImage = asset('storage/' . $backgrounds[array_rand($backgrounds)]);
                } catch (\Exception $e) {
                    return '';
                }

                return '<style>
                    .filament-sidebar {
                        background-color: transparent !important;
                    }
                    .filament-sidebar::before {
                        content: "";
                        position: absolute;
                        inset: -40px;
                        background-image: url("' . $bgImage . '");
                        background-size: cover;
                        background-position: center;
                        filter: blur(20px);
                        z-index: -2;
                    }
                    .filament-sidebar::after {
                        content: "";
                        position: absolute;
                        inset: 0;
                        z-index: -1;
                    }
                    html.dark .filament-sidebar::after {
                        background-color: rgba(17, 24, 39, 0.75);
                    }
                    html:not(.dark) .filament-sidebar::after {
                        background-color: rgba(255, 255, 255, 0.75);
                    }
                </style>';
            }
        );

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

        // Add custom meta (favicon)
        $favicon = config('app.favicon') ?: config('app.logo_dark') ?: config('app.logo');
        Filament::pushMeta([
            new HtmlString('<link rel="icon" href="' . $favicon . '">'),
            new HtmlString('<meta name="user-id" content="' . (auth()->id() ?? '') . '">'),
            new HtmlString('<meta name="user-birthday-today" content="' . (auth()->check() && auth()->user()->birthday && auth()->user()->birthday->format('m-d') === now()->format('m-d') ? '1' : '0') . '">'),
            new HtmlString('<meta name="any-birthday-today" content="' . (\App\Models\User::whereMonth('birthday', now()->month)->whereDay('birthday', now()->day)->exists() ? '1' : '0') . '">'),
        ]);

        // Register navigation groups (ordered by persona: daily work → self → collaborative → admin)
        Filament::registerNavigationGroups([
            __('Workspace'),
            __('Reports'),
            __('Performance'),
            __('Team'),
            __('Admin'),
        ]);

        // Accordion-style sidebar groups: collapsed by default, only one open at a time.
        // Patches Filament v2's native $store.sidebar.toggleCollapsedGroup.
        Filament::registerRenderHook(
            'body.end',
            fn (): string => auth()->check() ? <<<'HTML'
            <script>
            document.addEventListener('alpine:initialized', () => {
                const store = Alpine.store('sidebar');
                if (!store) return;

                const getAllLabels = () => Array.from(document.querySelectorAll('.filament-sidebar-group'))
                    .map(el => Alpine.$data(el)?.label)
                    .filter(l => typeof l === 'string' && l.length > 0);

                // On first visit (or if user manually cleared state), collapse everything.
                const labels = getAllLabels();
                if (labels.length > 0 && store.collapsedGroups.length === 0) {
                    store.collapsedGroups = labels;
                }

                // Override toggle: opening a group closes all its siblings.
                store.toggleCollapsedGroup = function (group) {
                    const all = getAllLabels();
                    if (this.collapsedGroups.includes(group)) {
                        // Opening this one → collapse all others
                        this.collapsedGroups = all.filter(l => l !== group);
                    } else {
                        // Closing this one → everything collapsed
                        this.collapsedGroups = Array.from(new Set([...this.collapsedGroups, group]));
                    }
                };
            });
            </script>
            HTML : '',
        );

        // Force HTTPS over HTTP
        if (env('APP_FORCE_HTTPS') ?? false) {
            URL::forceScheme('https');
        }

        Blade::component('user-avatar', \App\View\Components\UserAvatar::class);

        // Chat Bot Widget - inject on every page
        Filament::registerRenderHook(
            'body.end',
            fn (): string => auth()->check()
                ? Blade::render('@livewire("chat-widget")')
                : '',
        );

        // Messenger Widget - 1-on-1 team chat, injected at bottom-right (left of chatbot)
        // Auto-hidden on /dm (fullpage messenger) to avoid double-mounting the component.
        Filament::registerRenderHook(
            'body.end',
            fn (): string => auth()->check() && ! request()->is('dm')
                ? Blade::render('@livewire("messenger")')
                : '',
        );

        // Messenger topbar icon - sits between the bell and the user avatar.
        // Clicking it opens the fullpage view at /dm. Unread badge mirrors
        // the floating widget's total-unread count (fetched inline per-render).
        Filament::registerRenderHook(
            'user-menu.start',
            function (): string {
                if (! auth()->check()) {
                    return '';
                }
                $userId = (int) auth()->id();
                $unread = \App\Models\MessengerMessage::query()
                    ->whereHas('conversation', fn ($q) => $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId))
                    ->where('sender_id', '!=', $userId)
                    ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $userId))
                    ->whereNull('deleted_for_everyone_at')
                    ->count();
                $badge = $unread > 0
                    ? '<span style="position:absolute;top:4px;right:4px;min-width:16px;height:16px;padding:0 4px;background:#ef4444;color:#fff;font-size:10px;font-weight:600;border-radius:8px;display:flex;align-items:center;justify-content:center;line-height:1;">'.($unread > 99 ? '99+' : $unread).'</span>'
                    : '';
                return <<<HTML
                <a href="/dm" title="Messenger" style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;color:#6b7280;transition:background 0.15s, color 0.15s;" onmouseover="this.style.background='rgba(107,114,128,0.1)';this.style.color='#374151';" onmouseout="this.style.background='transparent';this.style.color='#6b7280';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-1m-5-4V6a2 2 0 012-2h7a2 2 0 012 2v5a2 2 0 01-2 2h-4l-4 4v-4H4a2 2 0 01-2-2z"/>
                    </svg>
                    $badge
                </a>
                HTML;
            },
        );

        // Command palette - modal + ⌘K/Ctrl+K keyboard handler (body.end)
        Filament::registerRenderHook(
            'body.end',
            fn (): string => auth()->check()
                ? Blade::render('@livewire("command-palette")')
                : '',
        );

        // Command palette trigger button - lives in the sidebar nav area so it doesn't
        // overlap the user menu. Dispatches a CustomEvent the modal component listens for.
        Filament::registerRenderHook(
            'sidebar.start',
            fn (): string => auth()->check() ? <<<'HTML'
            <div class="px-6 mb-4" x-data="{ isMac: /Mac|iPod|iPhone|iPad/.test(navigator.platform) }">
                <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('cmd-palette:toggle'))"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 hover:bg-white dark:hover:bg-gray-800 hover:border-indigo-400 dark:hover:border-indigo-500 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="flex-1 text-left text-[12.5px] font-medium">Search…</span>
                    <kbd class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-950 text-[10px] font-mono text-gray-500 dark:text-gray-400">
                        <span x-text="isMac ? '⌘' : 'Ctrl'"></span>K
                    </kbd>
                </button>
            </div>
            HTML : '',
        );

        // Version pill at the bottom of the sidebar. Click opens a modal that
        // shows the current version's changelog highlights with a link to
        // /changelog for the full history.
        Filament::registerRenderHook(
            'sidebar.end',
            function (): string {
                if (! auth()->check()) return '';
                $current = config('changelog.0', null);
                if (! $current) return '';
                $version = $current['version'] ?? '1.0.0';
                $title = $current['title'] ?? '';
                $date = !empty($current['released_at']) ? \Carbon\Carbon::parse($current['released_at'])->format('d M Y') : '';
                $highlights = $current['highlights'] ?? [];
                $bullets = collect($highlights)
                    ->map(fn ($h) => '<li style="position:relative;padding-left:20px;margin-bottom:6px;font-size:12.5px;line-height:1.5;color:#374151;"><span style="position:absolute;left:0;top:2px;width:14px;height:14px;display:inline-flex;align-items:center;justify-content:center;background:rgba(59,130,246,0.12);color:#2563eb;border-radius:50%;font-size:10px;font-weight:700;">✓</span>'.e($h).'</li>')
                    ->implode('');
                $fullUrl = url('/changelog');
                $escTitle = e($title);

                return <<<HTML
                <div style="padding: 14px 20px 16px; border-top: 1px solid rgba(148,163,184,0.15); margin-top: auto;"
                     x-data="{ open: false }">
                    <button type="button"
                            x-on:click="open = true"
                            style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:6px 10px; background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.25); border-radius:8px; color:#93c5fd; font-size:12px; font-weight:600; cursor:pointer; transition:background 0.15s;"
                            onmouseover="this.style.background='rgba(59,130,246,0.18)'"
                            onmouseout="this.style.background='rgba(59,130,246,0.08)'"
                            title="View changelog">
                        <span style="display:inline-flex; align-items:center; gap:6px;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#34d399;"></span>
                            <span>PMHelper v{$version}</span>
                        </span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>

                    <div x-show="open"
                         x-transition.opacity
                         style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9998; align-items:center; justify-content:center; padding:24px;"
                         x-bind:style="open ? 'display:flex' : 'display:none'"
                         x-on:click="open = false"
                         x-on:keydown.escape.window="open = false">
                        <div x-on:click.stop
                             style="background:#fff; color:#111827; border-radius:12px; max-width:640px; width:100%; max-height:85vh; overflow:auto; box-shadow:0 20px 60px rgba(0,0,0,0.4);"
                             class="dark:bg-gray-800 dark:text-gray-100">
                            <div style="padding:20px 24px; border-bottom:1px solid rgba(148,163,184,0.2); display:flex; align-items:center; justify-content:space-between; gap:12px;">
                                <div>
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <span style="font-size:20px; font-weight:700;">v{$version}</span>
                                        <span style="display:inline-block; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; background:#dbeafe; color:#1d4ed8;">What's new</span>
                                        <span style="font-size:12px; color:#6b7280;">{$date}</span>
                                    </div>
                                    <div style="font-size:14px; font-weight:600; margin-top:6px; color:#111827;" class="dark:text-gray-100">{$escTitle}</div>
                                </div>
                                <button type="button" x-on:click="open = false"
                                        style="background:transparent; border:none; color:#9ca3af; cursor:pointer; font-size:22px; line-height:1; padding:4px;">&times;</button>
                            </div>
                            <div style="padding:16px 24px 20px;">
                                <ul style="list-style:none; padding:0; margin:0;">
                                    {$bullets}
                                </ul>
                                <div style="margin-top:16px; padding-top:14px; border-top:1px solid rgba(148,163,184,0.2); text-align:right;">
                                    <a href="{$fullUrl}"
                                       style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; color:#2563eb; text-decoration:none;"
                                       onmouseover="this.style.textDecoration='underline'"
                                       onmouseout="this.style.textDecoration='none'">
                                        View full changelog
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                HTML;
            },
        );

        // Development banner - OKR/KPI module in progress (disable when user confirms complete)
        Filament::registerRenderHook(
            'body.end',
            fn (): string => auth()->check() && (bool) env('DEV_BANNER_OKR', true)
                ? '<div id="dev-banner-okr" style="position:fixed;bottom:16px;left:16px;z-index:9999;max-width:360px;background:#fef3c7;border:1px solid #f59e0b;border-left:4px solid #d97706;border-radius:8px;padding:10px 14px;box-shadow:0 4px 12px rgba(0,0,0,0.08);font-family:Inter,system-ui,sans-serif;font-size:12.5px;line-height:1.45;color:#78350f;display:flex;gap:10px;align-items:flex-start;">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="#d97706" style="flex-shrink:0;margin-top:1px;" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    <div>
                        <div style="font-weight:600;color:#78350f;margin-bottom:2px;">Development in progress</div>
                        <div style="color:#92400e;">The <strong>OKR &amp; KPI</strong> module is being built — minor disruptions possible.</div>
                    </div>
                    <button onclick="document.getElementById(\'dev-banner-okr\').style.display=\'none\'" style="background:none;border:none;color:#92400e;cursor:pointer;font-size:16px;line-height:1;padding:0 0 0 4px;flex-shrink:0;" title="Dismiss">&times;</button>
                </div>'
                : '',
        );

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

            // Configure favicon
            $favicon = isset($settings->site_favicon) && $settings->site_favicon
                ? asset('storage/' . $settings->site_favicon)
                : null;
            Config::set('app.favicon', $favicon);
            Config::set('filament.favicon', $favicon ?: ($settings->site_logo ? asset('storage/' . $settings->site_logo) : null));

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