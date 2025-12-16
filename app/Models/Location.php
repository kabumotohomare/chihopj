<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 地域モデル（都道府県・市区町村マスタ）
 */
class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'prefecture',
        'city',
    ];

    /**
     * 企業プロフィールとのリレーション
     */
    public function companyProfiles(): HasMany
    {
        return $this->hasMany(CompanyProfile::class);
    }

    /**
     * ワーカープロフィール（出身地）とのリレーション
     */
    public function workerProfilesAsBirthLocation(): HasMany
    {
        return $this->hasMany(WorkerProfile::class, 'birth_location_id');
    }

    /**
     * ワーカープロフィール（現在のお住まい1）とのリレーション
     */
    public function workerProfilesAsCurrentLocation1(): HasMany
    {
        return $this->hasMany(WorkerProfile::class, 'current_location_1_id');
    }

    /**
     * ワーカープロフィール（現在のお住まい2）とのリレーション
     */
    public function workerProfilesAsCurrentLocation2(): HasMany
    {
        return $this->hasMany(WorkerProfile::class, 'current_location_2_id');
    }

    /**
     * ワーカープロフィール（移住に関心のある地域1）とのリレーション
     */
    public function workerProfilesAsFavoriteLocation1(): HasMany
    {
        return $this->hasMany(WorkerProfile::class, 'favorite_location_1_id');
    }

    /**
     * ワーカープロフィール（移住に関心のある地域2）とのリレーション
     */
    public function workerProfilesAsFavoriteLocation2(): HasMany
    {
        return $this->hasMany(WorkerProfile::class, 'favorite_location_2_id');
    }

    /**
     * ワーカープロフィール（移住に関心のある地域3）とのリレーション
     */
    public function workerProfilesAsFavoriteLocation3(): HasMany
    {
        return $this->hasMany(WorkerProfile::class, 'favorite_location_3_id');
    }

    /**
     * 都道府県のみの地域を取得
     */
    public function scopePrefecturesOnly($query)
    {
        return $query->whereNull('city')->orderBy('code');
    }

    /**
     * 指定した都道府県の市区町村を取得
     */
    public function scopeCitiesInPrefecture($query, string $prefecture)
    {
        return $query->where('prefecture', $prefecture)
            ->whereNotNull('city')
            ->orderBy('code');
    }

    /**
     * 表示名を取得（都道府県 市区町村）
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->city ? "{$this->prefecture} {$this->city}" : $this->prefecture;
    }
}
