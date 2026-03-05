<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 応募データCSV同期エクスポート
 *
 * ボタン押下で即座にCSVファイルをダウンロードする。
 */
class JobApplicationCsvExport
{
    /** @var array<string, string> CSVヘッダー定義 */
    private const HEADERS = [
        '氏名',
        'メールアドレス',
        'ハンドルネーム',
        '性別',
        '生年月日',
        '現住所',
        '電話番号',
        '出身地',
        '居住地（都道府県）',
        '志望動機',
        '応募理由',
        'ステータス',
        '応募日',
        '判定日',
        '求人タイトル',
        '企業名',
    ];

    /**
     * CSVストリームレスポンスを生成
     *
     * @param  Builder|null  $query  ベースクエリ（省略時は全件）
     * @param  Carbon|null  $from  期間開始日（指定時は applied_at >= startOfDay）
     * @param  Carbon|null  $to  期間終了日（指定時は applied_at <= endOfDay）
     */
    public static function download(
        ?Builder $query = null,
        ?Carbon $from = null,
        ?Carbon $to = null,
    ): StreamedResponse {
        $query ??= JobApplication::query();

        if ($from !== null) {
            $query->where('applied_at', '>=', $from->copy()->startOfDay());
        }

        if ($to !== null) {
            $query->where('applied_at', '<=', $to->copy()->endOfDay());
        }

        $query->with([
            'worker.workerProfile.birthLocation',
            'worker.workerProfile.currentLocation1',
            'jobPost.company',
        ]);

        $filename = '応募一覧_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // BOM（Excel での文字化け防止）
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, self::HEADERS);

            // データ行（チャンクで処理）
            $query->chunk(500, function ($applications) use ($handle) {
                foreach ($applications as $application) {
                    $profile = $application->worker?->workerProfile;
                    fputcsv($handle, [
                        $application->worker?->name ?? '',
                        $application->worker?->email ?? '',
                        $profile?->handle_name ?? '',
                        self::formatGender($profile?->gender),
                        $profile?->birthdate?->format('Y/m/d') ?? '',
                        $profile?->current_address ?? '',
                        $profile?->phone_number ?? '',
                        $profile?->birthLocation?->prefecture ?? '',
                        $profile?->currentLocation1?->prefecture ?? '',
                        $application->motive ?? '',
                        is_array($application->reasons) ? implode('、', $application->reasons) : '',
                        self::formatStatus($application->status),
                        $application->applied_at?->format('Y/m/d H:i') ?? '',
                        $application->judged_at?->format('Y/m/d H:i') ?? '',
                        $application->jobPost?->job_title ?? '',
                        $application->jobPost?->company?->name ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * 性別を日本語に変換
     */
    private static function formatGender(?string $gender): string
    {
        return match ($gender) {
            'male' => '男性',
            'female' => '女性',
            'other' => 'その他',
            default => '',
        };
    }

    /**
     * ステータスを日本語に変換
     */
    private static function formatStatus(?string $status): string
    {
        return match ($status) {
            'applied' => '応募中',
            'accepted' => '承認',
            'rejected' => '不承認',
            default => '',
        };
    }
}
