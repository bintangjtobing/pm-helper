<?php

namespace App\Filament\Pages;

use App\Models\Role;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Actions\Action;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageGeneralSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $settings = GeneralSettings::class;

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('Manage general settings');
    }

    protected function getHeading(): string|Htmlable
    {
        return __('Manage general settings');
    }

    protected static function getNavigationLabel(): string
    {
        return __('General');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Grid::make(1)
                                ->columnSpan(1)
                                ->schema([
                                    FileUpload::make('site_logo')
                                        ->label(__('Site logo (Light Mode)'))
                                        ->helperText(__('Logo for light theme and general use'))
                                        ->image()
                                        ->maxSize(config('system.max_file_size')),

                                    FileUpload::make('site_logo_dark')
                                        ->label(__('Site logo (Dark Mode)'))
                                        ->helperText(__('Logo for dark theme (optional - will use light logo if not set)'))
                                        ->image()
                                        ->maxSize(config('system.max_file_size')),

                                    FileUpload::make('site_favicon')
                                        ->label(__('Favicon'))
                                        ->helperText(__('Browser tab icon (.ico, .png) - recommended 32x32 or 64x64 px'))
                                        ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/svg+xml'])
                                        ->maxSize(512),
                                ]),

                            Grid::make(1)
                                ->columnSpan(2)
                                ->schema([
                                    TextInput::make('site_name')
                                        ->label(__('Site name'))
                                        ->helperText(__('This is the platform name'))
                                        ->default(fn() => config('app.name'))
                                        ->required(),

                                    Toggle::make('enable_registration')
                                        ->label(__('Enable registration?'))
                                        ->helperText(__('If enabled, any user can create an account in this platform. But an administration need to give them permissions.')),

                                    Toggle::make('enable_social_login')
                                        ->label(__('Enable social login?'))
                                        ->helperText(__('If enabled, configured users can login via their social accounts.')),

                                    Toggle::make('enable_login_form')
                                        ->label(__('Enable form login?'))
                                        ->helperText(__('If enabled, a login form will be visible on the login page.')),

                                    Toggle::make('enable_oidc_login')
                                        ->label(__('Enable OIDC login?'))
                                        ->helperText(__('If enabled, an OIDC Connect button will be visible on the login page.')),

                                    Select::make('site_language')
                                        ->label(__('Site language'))
                                        ->helperText(__('The language used by the platform.'))
                                        ->searchable()
                                        ->options($this->getLanguages()),

                                    Select::make('default_role')
                                        ->label(__('Default role'))
                                        ->helperText(__('The platform default role (used mainly in OIDC Connect).'))
                                        ->searchable()
                                        ->options(Role::all()->pluck('name', 'id')->toArray()),
                                ]),
                        ]),
                ]),

            // Preview Card
            Card::make()
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('preview_title')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900">' . __('Logo Preview') . '</h3>
                                <p class="text-sm text-gray-600">' . __('Preview how your logos will appear in light and dark modes') . '</p>
                            </div>
                        ')),

                    Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('logo_preview_light')
                                ->label(__('Light Mode Preview'))
                                ->content(function ($get) {
                                    $logo = $get('site_logo');
                                    if ($logo) {
                                        if (is_array($logo)) {
                                            $first = reset($logo);
                                            $url = is_string($first) ? asset('storage/' . $first) : (is_object($first) ? $first->temporaryUrl() : null);
                                        } else {
                                            $url = is_string($logo) ? asset('storage/' . $logo) : $logo->temporaryUrl();
                                        }
                                        if (!$url) return new \Illuminate\Support\HtmlString('<div class="flex items-center justify-center p-4 text-gray-500 bg-white border border-gray-200 rounded-lg">Processing...</div>');
                                        return new \Illuminate\Support\HtmlString('
                                            <div class="flex items-center justify-center p-4 bg-white border border-gray-200 rounded-lg dark:bg-gray-100 dark:border-gray-300">
                                                <img src="' . $url . '" alt="Light Logo Preview" class="w-auto max-h-16">
                                            </div>
                                        ');
                                    }
                                    return new \Illuminate\Support\HtmlString('
                                        <div class="flex items-center justify-center p-4 text-gray-500 bg-white border border-gray-200 rounded-lg dark:bg-gray-100 dark:border-gray-300 dark:text-gray-600">
                                            No light logo uploaded
                                        </div>
                                    ');
                                }),

                            \Filament\Forms\Components\Placeholder::make('logo_preview_dark')
                                ->label(__('Dark Mode Preview'))
                                ->content(function ($get) {
                                    $darkLogo = $get('site_logo_dark');
                                    $lightLogo = $get('site_logo');

                                    if ($darkLogo) {
                                        if (is_array($darkLogo)) {
                                            $first = reset($darkLogo);
                                            $url = is_string($first) ? asset('storage/' . $first) : (is_object($first) ? $first->temporaryUrl() : null);
                                        } else {
                                            $url = is_string($darkLogo) ? asset('storage/' . $darkLogo) : $darkLogo->temporaryUrl();
                                        }
                                        if (!$url) $url = '';
                                        return new \Illuminate\Support\HtmlString('
                                            <div class="flex items-center justify-center p-4 bg-gray-800 border border-gray-600 rounded-lg dark:bg-gray-900 dark:border-gray-700">
                                                <img src="' . $url . '" alt="Dark Logo Preview" class="w-auto max-h-16">
                                            </div>
                                        ');
                                    } elseif ($lightLogo) {
                                        if (is_array($lightLogo)) {
                                            $first = reset($lightLogo);
                                            $url = is_string($first) ? asset('storage/' . $first) : (is_object($first) ? $first->temporaryUrl() : null);
                                        } else {
                                            $url = is_string($lightLogo) ? asset('storage/' . $lightLogo) : $lightLogo->temporaryUrl();
                                        }
                                        if (!$url) $url = '';
                                        return new \Illuminate\Support\HtmlString('
                                            <div class="relative flex items-center justify-center p-4 bg-gray-800 border border-gray-600 rounded-lg dark:bg-gray-900 dark:border-gray-700">
                                                <img src="' . $url . '" alt="Light Logo (Inverted)" class="w-auto max-h-16 filter brightness-0 invert">
                                                <div class="absolute px-2 py-1 text-xs text-yellow-900 bg-yellow-500 rounded bottom-2 right-2">
                                                    Auto-inverted
                                                </div>
                                            </div>
                                        ');
                                    }
                                    return new \Illuminate\Support\HtmlString('
                                        <div class="flex items-center justify-center p-4 text-gray-400 bg-gray-800 border border-gray-600 rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-gray-500">
                                            <div class="text-center">
                                                <div>No dark logo uploaded</div>
                                                <small>Will use light logo with auto-invert</small>
                                            </div>
                                        </div>
                                    ');
                                }),
                        ])
                ]),

            // Login Page Backgrounds
            Card::make()
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('login_bg_heading')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="mb-2">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">' . __('Login Page Backgrounds') . '</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">' . __('Upload up to 5 background images for the login page. A random image will be shown each time.') . '</p>
                            </div>
                        ')),

                    FileUpload::make('login_backgrounds')
                        ->label(__('Background Images'))
                        ->helperText(__('Recommended: 1200 x 1800 px (portrait 2:3), JPG/PNG. Images over 1MB will be auto-compressed.'))
                        ->multiple()
                        ->maxFiles(5)
                        ->image()
                        ->maxSize(10240)
                        ->enableReordering(),

                    Select::make('default_auth_theme')
                        ->label(__('Login page theme'))
                        ->helperText(__('Default theme for login and reset password pages'))
                        ->options([
                            'dark' => __('Dark mode'),
                            'light' => __('Light mode'),
                            'system' => __('Follow system preference'),
                        ])
                        ->required(),
                ]),

            // Weekly Report Settings
            Card::make()
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('weekly_report_heading')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="mb-2">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">' . __('Weekly Report Settings') . '</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">' . __('Configure weekly report submission settings for your team') . '</p>
                            </div>
                        ')),

                    Grid::make(2)
                        ->schema([
                            Select::make('report_due_day')
                                ->label(__('Report due day'))
                                ->helperText(__('The day of the week when weekly reports are due'))
                                ->options([
                                    'monday' => __('Monday'),
                                    'tuesday' => __('Tuesday'),
                                    'wednesday' => __('Wednesday'),
                                    'thursday' => __('Thursday'),
                                    'friday' => __('Friday'),
                                    'saturday' => __('Saturday'),
                                    'sunday' => __('Sunday'),
                                ])
                                ->required(),

                            Toggle::make('report_reminder_enabled')
                                ->label(__('Enable report reminder?'))
                                ->helperText(__('If enabled, team members will receive a reminder notification on the due day')),
                        ]),
                ]),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['login_backgrounds'])) {
            $data['login_backgrounds'] = array_map(function ($path) {
                return $this->compressImageIfNeeded($path);
            }, $data['login_backgrounds']);
        }

        return $data;
    }

    private function compressImageIfNeeded(string $path, int $maxBytes = 1048576): string
    {
        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath) || filesize($fullPath) <= $maxBytes) {
            return $path;
        }

        $info = getimagesize($fullPath);
        if (!$info) {
            return $path;
        }

        $mime = $info['mime'];
        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($fullPath),
            'image/png' => imagecreatefrompng($fullPath),
            'image/webp' => imagecreatefromwebp($fullPath),
            default => null,
        };

        if (!$image) {
            return $path;
        }

        // Progressively lower quality until under 1MB
        $quality = 85;
        do {
            ob_start();
            imagejpeg($image, null, $quality);
            $compressed = ob_get_clean();
            $quality -= 10;
        } while (strlen($compressed) > $maxBytes && $quality >= 20);

        // If still too large, also scale down
        if (strlen($compressed) > $maxBytes) {
            $w = imagesx($image);
            $h = imagesy($image);
            $ratio = sqrt($maxBytes / strlen($compressed));
            $newW = (int) ($w * $ratio);
            $newH = (int) ($h * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($image);
            $image = $resized;

            ob_start();
            imagejpeg($image, null, 75);
            $compressed = ob_get_clean();
        }

        // Save as JPG (replace original)
        $jpgPath = preg_replace('/\.(png|webp)$/i', '.jpg', $path);
        $jpgFullPath = storage_path('app/public/' . $jpgPath);
        file_put_contents($jpgFullPath, $compressed);

        // Remove original if different file
        if ($jpgPath !== $path && file_exists($fullPath)) {
            unlink($fullPath);
        }

        imagedestroy($image);

        return $jpgPath;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label(__('Save'));
    }

    private function getLanguages(): array
    {
        $languages = config('system.locales.list');
        asort($languages);
        return $languages;
    }
}
