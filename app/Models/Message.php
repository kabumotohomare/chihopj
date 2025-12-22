<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * チャットメッセージモデル
 */
class Message extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_room_id',
        'sender_id',
        'message',
        'is_read',
    ];

    /**
     * 属性のキャスト
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    /**
     * チャットルームとのリレーション
     */
    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    /**
     * 送信者（ユーザー）とのリレーション
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * 既読にする
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * 未読かどうか
     */
    public function isUnread(): bool
    {
        return ! $this->is_read;
    }
}
