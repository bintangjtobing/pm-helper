<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Laravel\Sanctum\PersonalAccessToken;

class McpTokens extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $slug = 'mcp-tokens';

    protected static string $view = 'filament.pages.mcp-tokens';

    protected static ?int $navigationSort = 90;

    public ?string $plaintextToken = null;
    public ?string $plaintextTokenName = null;

    protected static function getNavigationLabel(): string
    {
        return __('MCP Tokens');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return null;
    }

    protected function getTitle(): string
    {
        return __('MCP Tokens');
    }

    public function getTokens()
    {
        return PersonalAccessToken::query()
            ->where('tokenable_type', get_class(auth()->user()))
            ->where('tokenable_id', auth()->id())
            ->where('name', 'like', 'mcp:%')
            ->orderByDesc('created_at')
            ->get();
    }

    protected function getActions(): array
    {
        return [
            Action::make('createToken')
                ->label(__('New MCP token'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->button()
                ->modalHeading(__('Create MCP token'))
                ->modalSubheading(__('Give this token a name (e.g. "Bintang MacBook"). The token string is shown only once on the next screen — save it somewhere safe.'))
                ->modalButton(__('Create token'))
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Token name'))
                        ->required()
                        ->maxLength(100)
                        ->placeholder(__('e.g. Bintang MacBook')),
                ])
                ->action(function (array $data): void {
                    $label = trim((string) $data['name']);
                    if ($label === '') {
                        return;
                    }

                    $full = auth()->user()->createToken('mcp:' . $label, ['mcp:*']);
                    $this->plaintextToken = $full->plainTextToken;
                    $this->plaintextTokenName = $label;

                    Notification::make()
                        ->success()
                        ->title(__('Token created'))
                        ->body(__('Copy the token now — it won\'t be shown again.'))
                        ->send();
                }),
        ];
    }

    public function dismissToken(): void
    {
        $this->plaintextToken = null;
        $this->plaintextTokenName = null;
    }

    public function revoke(int $id): void
    {
        $token = PersonalAccessToken::query()
            ->where('tokenable_type', get_class(auth()->user()))
            ->where('tokenable_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (! $token) {
            Notification::make()->danger()->title(__('Token not found'))->send();
            return;
        }

        $token->delete();

        Notification::make()
            ->success()
            ->title(__('Token revoked'))
            ->body($token->name . ' ' . __('can no longer access the MCP endpoint.'))
            ->send();
    }
}
