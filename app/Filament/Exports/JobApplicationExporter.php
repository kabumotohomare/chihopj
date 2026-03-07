<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\JobApplication;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

/**
 * 応募データCSVエクスポーター
 *
 * 応募者情報・応募内容・求人情報をCSVとして出力する。
 * Admin / Government 両パネルから共通利用する。
 */
class JobApplicationExporter extends Exporter
{
    /** @var class-string */
    protected static ?string $model = JobApplication::class;

    /**
     * CSVカラム定義
     *
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            // 応募者情報
            ExportColumn::make('worker.name')
                ->label('氏名'),
            ExportColumn::make('worker.email')
                ->label('メールアドレス'),
            ExportColumn::make('worker.workerProfile.handle_name')
                ->label('ハンドルネーム'),
            ExportColumn::make('worker.workerProfile.gender')
                ->label('性別')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'male' => '男性',
                    'female' => '女性',
                    'other' => 'その他',
                    default => '',
                }),
            ExportColumn::make('worker.workerProfile.birthdate')
                ->label('生年月日'),
            ExportColumn::make('worker.workerProfile.current_address')
                ->label('現住所'),
            ExportColumn::make('worker.workerProfile.phone_number')
                ->label('電話番号'),
            ExportColumn::make('worker.workerProfile.birthLocation.prefecture')
                ->label('出身地'),
            ExportColumn::make('worker.workerProfile.currentLocation1.prefecture')
                ->label('居住地（都道府県）'),

            // 応募情報
            ExportColumn::make('motive')
                ->label('志望動機'),
            ExportColumn::make('reasons')
                ->label('応募理由')
                ->formatStateUsing(fn ($state): string => is_array($state) ? implode('、', $state) : (string) ($state ?? '')),
            ExportColumn::make('status')
                ->label('ステータス')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'applied' => '応募中',
                    'accepted' => '承認',
                    'rejected' => '不承認',
                    default => '',
                }),
            ExportColumn::make('applied_at')
                ->label('応募日'),
            ExportColumn::make('judged_at')
                ->label('判定日'),

            // 求人情報
            ExportColumn::make('jobPost.job_title')
                ->label('求人タイトル'),
            ExportColumn::make('jobPost.company.name')
                ->label('企業名'),
        ];
    }

    /**
     * エクスポート完了通知のタイトル
     */
    public static function getCompletedNotificationBody(Export $export): string
    {
        $successCount = number_format($export->successful_rows);

        return "応募データのエクスポートが完了しました。{$successCount}件を出力しました。";
    }
}
