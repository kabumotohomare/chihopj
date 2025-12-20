<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\JobPost;
use App\Models\JobPostSuggestion;

class JobPostObserver
{
    /**
     * Handle the JobPost "created" event.
     */
    public function created(JobPost $jobPost): void
    {
        $this->addToSuggestions($jobPost);
    }

    /**
     * Handle the JobPost "updated" event.
     */
    public function updated(JobPost $jobPost): void
    {
        // job_detailが変更された場合のみ追加
        if ($jobPost->wasChanged('job_detail')) {
            $this->addToSuggestions($jobPost);
        }
    }

    /**
     * job_detailを入力補助データに追加
     */
    protected function addToSuggestions(JobPost $jobPost): void
    {
        // job_detailが空または短すぎる場合はスキップ
        if (empty($jobPost->job_detail) || mb_strlen($jobPost->job_detail) < 10) {
            return;
        }

        // 既に同じフレーズが存在する場合は使用回数をインクリメントのみ
        $existing = JobPostSuggestion::query()
            ->where('phrase', $jobPost->job_detail)
            ->first();

        if ($existing) {
            $existing->incrementUsage();

            return;
        }

        // 新規追加：カテゴリを自動判定
        $category = $this->detectCategory($jobPost->job_detail);

        JobPostSuggestion::create([
            'phrase' => $jobPost->job_detail,
            'category' => $category,
            'usage_count' => 1, // 初回使用として1にセット
        ]);
    }

    /**
     * job_detailの内容からカテゴリを自動判定
     */
    protected function detectCategory(string $jobDetail): string
    {
        // カテゴリ判定用のキーワードマップ
        $categoryKeywords = [
            '宿泊業' => ['旅館', '宿泊', 'ホテル', '民宿', 'ゲストハウス', '客室', 'フロント', 'OTA', 'チェックイン'],
            '飲食業' => ['飲食店', 'レストラン', 'カフェ', '食堂', 'パン屋', 'ベーカリー', 'メニュー', '調理', '厨房', '接客', 'ランチ', 'ディナー', 'テイクアウト'],
            '小売業' => ['花屋', '小売', '販売店', '店舗', '在庫', 'POS', 'レジ', 'EC', '通販', '商品'],
            '農業' => ['農業', '農家', '農作物', '栽培', '収穫', '田んぼ', '畑', 'ハウス', '就農', '直売', '農産物'],
            '製造業' => ['製造', '工場', '生産', '工房', '加工', '職人', '技術', '原材料', '納期', 'どぶろく', '醸造', '伝統工芸', 'FAX'],
            '士業' => ['税務', '会計', '確定申告', '相続', '顧問', '申告書', '給与計算', '年末調整', '社労士', '税理士'],
            '自動車販売' => ['自動車販売', 'ドライバー', 'スーパーバイザー', '営業車', '配送', '運送'],
            '介護' => ['介護', '診療所', '老人保健施設', 'グループホーム', '介護報酬', '医療', 'デイサービス', '福祉'],
            '観光業' => ['観光', '送り火', 'イベント', '祭り', 'ツアー', 'ガイド'],
        ];

        // キーワードマッチングでカテゴリを判定
        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($jobDetail, $keyword)) {
                    return $category;
                }
            }
        }

        // どのカテゴリにも該当しない場合は「共通」
        return '共通';
    }

    /**
     * Handle the JobPost "deleted" event.
     */
    public function deleted(JobPost $jobPost): void
    {
        //
    }

    /**
     * Handle the JobPost "restored" event.
     */
    public function restored(JobPost $jobPost): void
    {
        //
    }

    /**
     * Handle the JobPost "force deleted" event.
     */
    public function forceDeleted(JobPost $jobPost): void
    {
        //
    }
}
