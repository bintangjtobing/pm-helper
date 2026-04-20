<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    protected static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Full name'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('username')
                                    ->label(__('Username'))
                                    ->unique(User::class, 'username', ignoreRecord: true)
                                    ->helperText(__('Leave empty to auto-generate from email'))
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label(__('Email address'))
                                    ->email()
                                    ->required()
                                    ->rule(
                                        fn($record) => 'unique:users,email,'
                                            . ($record ? $record->id : 'NULL')
                                            . ',id,deleted_at,NULL'
                                    )
                                    ->maxLength(255),

                                Forms\Components\CheckboxList::make('roles')
                                    ->label(__('Permission roles'))
                                    ->required()
                                    ->columns(3)
                                    ->relationship('roles', 'name'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('gender')
                                    ->label(__('Gender'))
                                    ->options([
                                        'male' => __('Male'),
                                        'female' => __('Female'),
                                        'other' => __('Other'),
                                    ]),

                                Forms\Components\DatePicker::make('birthday')
                                    ->label(__('Birthday'))
                                    ->maxDate(now()->subYears(16)),

                                Forms\Components\Select::make('supervisor_id')
                                    ->label(__('Direct Supervisor'))
                                    ->options(fn ($record) => User::where('id', '!=', $record?->id ?? 0)->orderBy('name')->pluck('name', 'id'))
                                    ->searchable(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('department_id')
                                    ->label(__('Department'))
                                    ->options(Department::orderBy('sort_order')->pluck('name', 'id'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('position_id', null)),

                                Forms\Components\Select::make('position_id')
                                    ->label(__('Position'))
                                    ->options(function (callable $get) {
                                        $deptId = $get('department_id');
                                        if (!$deptId) return [];
                                        return Position::where('department_id', $deptId)->orderBy('sort_order')->pluck('name', 'id');
                                    })
                                    ->searchable(),
                            ]),
                    ])
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['department', 'position', 'supervisor', 'roles']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\ViewColumn::make('user_card')
                        ->view('partials.filament.resources.user-card-column')
                        ->searchable(query: function ($query, string $search) {
                            $query->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                  ->orWhere('username', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                            });
                        }),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
