<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * 管理者パネル用 ユーザー管理リソース
 */
class UserResource extends Resource
{
    /** @var class-string */
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'ユーザー管理';

    protected static ?string $modelLabel = 'ユーザー';

    protected static ?string $pluralModelLabel = 'ユーザー管理';

    protected static string|\UnitEnum|null $navigationGroup = 'システム管理';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('氏名')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('メールアドレス')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('パスワード')
                    ->password()
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->helperText('編集時は空欄のままにすると変更されません'),
                Select::make('role')
                    ->label('ロール')
                    ->options([
                        'admin' => '管理者',
                        'municipal' => '役所',
                        'worker' => 'ワーカー',
                        'company' => '企業',
                    ])
                    ->required(),
                Select::make('spatie_roles')
                    ->label('Spatieロール')
                    ->multiple()
                    ->options(fn (): array => Role::query()
                        ->where('guard_name', 'web')
                        ->pluck('name', 'name')
                        ->toArray())
                    ->afterStateHydrated(function (Select $component, ?User $record): void {
                        if ($record) {
                            $component->state($record->getRoleNames()->toArray());
                        }
                    })
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('氏名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('メールアドレス')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('ロール')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => '管理者',
                        'municipal' => '役所',
                        'worker' => 'ワーカー',
                        'company' => '企業',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'municipal' => 'info',
                        'worker' => 'success',
                        'company' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Spatieロール')
                    ->badge()
                    ->separator(','),
                TextColumn::make('created_at')
                    ->label('登録日')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('ロール')
                    ->options([
                        'admin' => '管理者',
                        'municipal' => '役所',
                        'worker' => 'ワーカー',
                        'company' => '企業',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
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
