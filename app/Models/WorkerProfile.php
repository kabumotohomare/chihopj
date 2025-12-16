<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ワーカープロフィールモデル
 */
class WorkerProfile extends Model
{
    /** @use HasFactory<\Database\Factories\WorkerProfileFactory> */
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'handle_name',
        'icon',
        'gender',
        'birthdate',
        'experiences',
        'want_to_do',
        'good_contribution',
        'birth_location_id',
        'current_location_1_id',
        'current_location_2_id',
        'favorite_location_1_id',
        'favorite_location_2_id',
        'favorite_location_3_id',
        'available_action',
    ];

    /**
     * キャストする属性
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'available_action' => 'array',
        ];
    }

    /**
     * ユーザーとのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 出身地とのリレーション
     */
    public function birthLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'birth_location_id');
    }

    /**
     * 現在のお住まい1とのリレーション
     */
    public function currentLocation1(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_1_id');
    }

    /**
     * 現在のお住まい2とのリレーション
     */
    public function currentLocation2(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_2_id');
    }

    /**
     * 移住に関心のある地域1とのリレーション
     */
    public function favoriteLocation1(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'favorite_location_1_id');
    }

    /**
     * 移住に関心のある地域2とのリレーション
     */
    public function favoriteLocation2(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'favorite_location_2_id');
    }

    /**
     * 移住に関心のある地域3とのリレーション
     */
    public function favoriteLocation3(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'favorite_location_3_id');
    }

    /**
     * 年齢を取得
     */
    public function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->birthdate?->age,
        );
    }

    /**
     * 性別の日本語表示を取得
     */
    public function genderLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->gender) {
                'male' => '男性',
                'female' => '女性',
                'other' => 'その他',
                default => '未設定',
            },
        );
    }

    /**
     * 興味のあるお手伝いの日本語ラベルを取得
     *
     * @return array<string>
     */
    public function getAvailableActionLabelsAttribute(): array
    {
        if (! $this->available_action) {
            return [];
        }

        $labels = [
            'mowing' => '草刈り',
            'snowplow' => '雪かき',
            'diy' => 'DIY',
            'localcleaning' => '地域清掃',
            'volunteer' => '災害ボランティア',
        ];

        return array_map(
            fn ($action) => $labels[$action] ?? $action,
            $this->available_action
        );
    }
}
