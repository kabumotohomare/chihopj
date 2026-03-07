<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * 募集形態・希望・できますのマスタデータ
 *
 * type=0: コード種類定義
 * type=1: 募集形態
 * type=2: 希望
 * type=3: できます
 *
 * 修正:WinLogic - 募集一覧で Code テーブルへの N+1 クエリが発生していた（12件表示で24回の余分なクエリ）ため、
 * 全コードを1回のクエリで DB キャッシュし type 別にメモリ内フィルタリングする方式に変更。
 * モデルの作成・更新・削除時にキャッシュを自動クリアし、リアルタイム反映を保証。
 */
class Code extends Model
{
    /** キャッシュキー */
    private const CACHE_KEY = 'codes_all';

    /** キャッシュ有効期限（秒）: 24時間 */
    private const CACHE_TTL = 86400;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'type_id',
        'name',
        'description',
        'sort_order',
    ];

    /**
     * 属性のキャスト
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'type_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * モデルイベントでキャッシュを自動クリア
     */
    protected static function booted(): void
    {
        $clearCache = fn () => Cache::forget(self::CACHE_KEY);

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }

    /**
     * 全コードをキャッシュから取得（1クエリでN+1を解消）
     */
    public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::query()->orderBy('type')->orderBy('sort_order')->get();
        });
    }

    /**
     * キャッシュを手動クリア
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * 特定のtypeのコードをキャッシュから取得
     */
    public static function getByType(int $type): \Illuminate\Database\Eloquent\Collection
    {
        $all = self::getAllCached();

        // キャッシュからフィルタリング（DBクエリなし）
        $filtered = $all->where('type', $type)->sortBy('sort_order')->values();

        return new \Illuminate\Database\Eloquent\Collection($filtered->all());
    }

    /**
     * type_id のリストからコードを取得（getWantYouCodes/getCanDoCodes用）
     *
     * @param  int  $type  コード種別（2=希望、3=できます）
     * @param  array<int>  $typeIds  type_id の配列
     * @return \Illuminate\Support\Collection<int, static>
     */
    public static function getByTypeAndIds(int $type, array $typeIds): \Illuminate\Support\Collection
    {
        if (empty($typeIds)) {
            return collect();
        }

        $all = self::getAllCached();

        return $all
            ->where('type', $type)
            ->whereIn('type_id', $typeIds)
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * コード種類定義を取得 (type=0)
     */
    public static function getCodeTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return self::getByType(0);
    }

    /**
     * 募集形態を取得 (type=1)
     */
    public static function getRecruitmentTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return self::getByType(1);
    }

    /**
     * 希望を取得 (type=2)
     */
    public static function getRequests(): \Illuminate\Database\Eloquent\Collection
    {
        return self::getByType(2);
    }

    /**
     * できますを取得 (type=3)
     */
    public static function getOffers(): \Illuminate\Database\Eloquent\Collection
    {
        return self::getByType(3);
    }
}
