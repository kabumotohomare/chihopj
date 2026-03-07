<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityLogResource\Pages;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

/**
 * 管理者パネル用 操作ログリソース
 */
class ActivityLogResource extends Resource
{
    /** @var class-string */
    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = '操作ログ';

    protected static ?string $modelLabel = '操作ログ';

    protected static ?string $pluralModelLabel = '操作ログ';

    protected static string|\UnitEnum|null $navigationGroup = 'システム管理';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('日時')
                    ->dateTime('Y/m/d H:i:s')
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('操作者')
                    ->default('システム')
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->label('対象モデル')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->sortable(),
                TextColumn::make('event')
                    ->label('操作内容')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => '作成',
                        'updated' => '更新',
                        'deleted' => '削除',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('subject_id')
                    ->label('対象ID')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('対象モデル')
                    ->options(fn (): array => Activity::query()
                        ->distinct()
                        ->whereNotNull('subject_type')
                        ->pluck('subject_type')
                        ->mapWithKeys(fn (string $type) => [$type => class_basename($type)])
                        ->toArray()),
                SelectFilter::make('event')
                    ->label('操作種別')
                    ->options([
                        'created' => '作成',
                        'updated' => '更新',
                        'deleted' => '削除',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
