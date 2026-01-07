<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 募集投稿モデル
 */
class JobPost extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'eyecatch',
        'purpose',
        'job_title',
        'job_detail',
        'want_you_ids',
        'can_do_ids',
        'posted_at',
    ];

    /**
     * 属性のキャスト
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'want_you_ids' => 'array',
            'can_do_ids' => 'array',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * 企業ユーザーとのリレーション
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    /**
     * 募集形態とのリレーション
     */
    public function jobType(): BelongsTo
    {
        return $this->belongsTo(Code::class, 'job_type_id');
    }

    /**
     * 応募情報とのリレーション
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    /**
     * 募集目的の日本語表示を取得
     */
    public function getPurposeLabel(): string
    {
        return match ($this->purpose) {
            'want_to_do' => 'いつかやりたい',
            'need_help' => '人手が足りない',
            default => '不明',
        };
    }

    /**
     * 希望のコード情報を取得
     *
     * @return \Illuminate\Support\Collection<int, Code>
     */
    public function getWantYouCodes(): \Illuminate\Support\Collection
    {
        if (empty($this->want_you_ids)) {
            return collect();
        }

        return Code::query()->whereIn('id', $this->want_you_ids)->get();
    }

    /**
     * できますのコード情報を取得
     *
     * @return \Illuminate\Support\Collection<int, Code>
     */
    public function getCanDoCodes(): \Illuminate\Support\Collection
    {
        if (empty($this->can_do_ids)) {
            return collect();
        }

        return Code::query()->whereIn('id', $this->can_do_ids)->get();
    }
}
