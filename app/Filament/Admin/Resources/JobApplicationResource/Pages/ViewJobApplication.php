<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\JobApplicationResource\Pages;

use App\Filament\Admin\Resources\JobApplicationResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * 管理者パネル用 応募詳細ページ
 */
class ViewJobApplication extends ViewRecord
{
    protected static string $resource = JobApplicationResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('求人情報')
                    ->schema([
                        TextEntry::make('jobPost.job_title')
                            ->label('求人タイトル'),
                        TextEntry::make('jobPost.company.name')
                            ->label('企業名'),
                    ])
                    ->columns(2),

                Section::make('応募者情報')
                    ->schema([
                        TextEntry::make('worker.name')
                            ->label('氏名'),
                        TextEntry::make('worker.email')
                            ->label('メールアドレス'),
                        TextEntry::make('worker.workerProfile.handle_name')
                            ->label('ハンドルネーム'),
                        TextEntry::make('worker.workerProfile.gender')
                            ->label('性別')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'male' => '男性',
                                'female' => '女性',
                                'other' => 'その他',
                                default => '未設定',
                            }),
                        TextEntry::make('worker.workerProfile.birthdate')
                            ->label('生年月日')
                            ->date('Y/m/d'),
                        TextEntry::make('worker.workerProfile.current_address')
                            ->label('現住所'),
                        TextEntry::make('worker.workerProfile.phone_number')
                            ->label('電話番号'),
                    ])
                    ->columns(2),

                Section::make('応募内容')
                    ->schema([
                        TextEntry::make('motive')
                            ->label('志望動機'),
                        TextEntry::make('reasons')
                            ->label('応募理由')
                            ->formatStateUsing(fn ($state): string => is_array($state) ? implode('、', $state) : (string) ($state ?? '')),
                        TextEntry::make('status')
                            ->label('ステータス')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'applied' => '応募中',
                                'accepted' => '承認',
                                'rejected' => '不承認',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'applied' => 'warning',
                                'accepted' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('applied_at')
                            ->label('応募日')
                            ->dateTime('Y/m/d H:i'),
                        TextEntry::make('judged_at')
                            ->label('判定日')
                            ->dateTime('Y/m/d H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
