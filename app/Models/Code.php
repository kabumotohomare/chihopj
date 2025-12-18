<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 募集形態・希望・できますのマスタデータ
 *
 * type=0: コード種類定義
 * type=1: 募集形態
 * type=2: 希望
 * type=3: できます
 */
class Code extends Model
{
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
     * 特定のtypeのコードを取得
     */
    public static function getByType(int $type): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()
            ->where('type', $type)
            ->orderBy('sort_order')
            ->get();
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
