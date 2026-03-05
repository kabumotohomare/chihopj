<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * アクティビティログの設定
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        // nameがnullの場合は空文字を返す
        if (empty($this->name)) {
            return '';
        }

        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * 企業プロフィールとのリレーション
     */
    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    /**
     * ワーカープロフィールとのリレーション
     */
    public function workerProfile(): HasOne
    {
        return $this->hasOne(WorkerProfile::class);
    }

    /**
     * ワーカーとしての応募履歴とのリレーション
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'worker_id');
    }

    /**
     * 送信したメッセージとのリレーション
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * 企業ユーザーかどうか
     */
    public function isCompany(): bool
    {
        return $this->role === 'company';
    }

    /**
     * ワーカーユーザーかどうか
     */
    public function isWorker(): bool
    {
        return $this->role === 'worker';
    }

    /**
     * ゲストユーザーかどうか（ワーカーだがプロフィール未登録）
     */
    public function isGuest(): bool
    {
        return $this->role === 'worker' && ! $this->workerProfile;
    }

    /**
     * 管理者ユーザーかどうか
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * 役所ユーザーかどうか
     */
    public function isMunicipal(): bool
    {
        return $this->role === 'municipal';
    }

    /**
     * Filamentパネルへのアクセス可否を判定
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isAdmin(),
            'government' => $this->isMunicipal(),
            default => false,
        };
    }
}
