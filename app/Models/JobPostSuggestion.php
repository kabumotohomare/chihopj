<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPostSuggestion extends Model
{
    /**
     * 複数代入可能な属性
     *
     * @var array<string>
     */
    protected $fillable = [
        'phrase',
        'category',
        'usage_count',
    ];

    /**
     * 属性のキャスト
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
        ];
    }

    /**
     * 前方一致検索スコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByPrefix($query, string $prefix, int $limit = 5)
    {
        return $query
            ->where('phrase', 'like', $prefix.'%')
            ->orderBy('usage_count', 'desc')
            ->limit($limit);
    }

    /**
     * 使用回数をインクリメント
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
