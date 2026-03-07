<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * チャットルームモデル
 */
class ChatRoom extends Model
{
    use HasFactory, LogsActivity;

    /**
     * アクティビティログの設定
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
    ];

    /**
     * 応募情報とのリレーション
     */
    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /**
     * メッセージとのリレーション
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'chat_room_id');
    }
}
