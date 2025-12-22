<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 応募状況モデル
 */
class JobApplication extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'job_id',
        'worker_id',
        'motive',
        'reasons',
        'status',
        'applied_at',
        'judged_at',
        'declined_at',
    ];

    /**
     * 属性のキャスト
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reasons' => 'array',
            'applied_at' => 'datetime',
            'judged_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    /**
     * 募集投稿とのリレーション
     */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }

    /**
     * ワーカーユーザーとのリレーション
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /**
     * チャットルームとのリレーション
     */
    public function chatRoom(): HasOne
    {
        return $this->hasOne(ChatRoom::class, 'application_id');
    }

    /**
     * ステータスの日本語表示を取得
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'applied' => '応募中',
            'accepted' => '承認',
            'rejected' => '不承認',
            'declined' => '辞退',
            default => '不明',
        };
    }

    /**
     * 応募中かどうか
     */
    public function isApplied(): bool
    {
        return $this->status === 'applied';
    }

    /**
     * 承認済みかどうか
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * 不承認かどうか
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * 辞退したかどうか
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }
}
