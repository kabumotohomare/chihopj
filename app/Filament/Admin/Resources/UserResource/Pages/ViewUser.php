<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * 管理者パネル用 ユーザー詳細ページ
 */
class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ユーザー情報')
                    ->schema([
                        TextEntry::make('name')
                            ->label('氏名'),
                        TextEntry::make('email')
                            ->label('メールアドレス'),
                        TextEntry::make('role')
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
                            }),
                        TextEntry::make('roles.name')
                            ->label('Spatieロール')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label('登録日')
                            ->dateTime('Y/m/d H:i'),
                        TextEntry::make('updated_at')
                            ->label('更新日')
                            ->dateTime('Y/m/d H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
