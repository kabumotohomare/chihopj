<?php

declare(strict_types=1);

namespace App\Filament\Government\Resources\ActivityLogResource\Pages;

use App\Filament\Government\Resources\ActivityLogResource;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * 役所パネル用 操作ログ詳細ページ
 */
class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ログ情報')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('日時')
                            ->dateTime('Y/m/d H:i:s'),
                        TextEntry::make('causer.name')
                            ->label('操作者')
                            ->default('システム'),
                        TextEntry::make('causer.email')
                            ->label('操作者メール')
                            ->default('-'),
                        TextEntry::make('subject_type')
                            ->label('対象モデル')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                        TextEntry::make('subject_id')
                            ->label('対象ID'),
                        TextEntry::make('event')
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
                            }),
                        TextEntry::make('description')
                            ->label('説明'),
                    ])
                    ->columns(2),

                Section::make('変更内容')
                    ->schema([
                        KeyValueEntry::make('properties.old')
                            ->label('変更前'),
                        KeyValueEntry::make('properties.attributes')
                            ->label('変更後'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record): bool => ! empty($record->properties['old'] ?? null)),
            ]);
    }
}
