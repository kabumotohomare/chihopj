<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\User;
use App\Models\WorkerProfile;

test('企業ユーザーを作成できる', function () {
    $user = User::factory()->create([
        'name' => 'テスト企業',
        'email' => 'company@example.com',
        'role' => 'company',
    ]);

    expect($user->role)->toBe('company')
        ->and($user->isCompany())->toBeTrue()
        ->and($user->isWorker())->toBeFalse();
});

test('ワーカーユーザーを作成できる', function () {
    $user = User::factory()->create([
        'name' => 'テストユーザー',
        'email' => 'worker@example.com',
        'role' => 'worker',
    ]);

    expect($user->role)->toBe('worker')
        ->and($user->isWorker())->toBeTrue()
        ->and($user->isCompany())->toBeFalse();
});

test('企業ユーザーは企業プロフィールとリレーションを持つ', function () {
    $user = User::factory()->create(['role' => 'company']);
    $profile = CompanyProfile::factory()->create(['user_id' => $user->id]);

    expect($user->companyProfile)->toBeInstanceOf(CompanyProfile::class)
        ->and($user->companyProfile->id)->toBe($profile->id);
});

test('ワーカーユーザーはワーカープロフィールとリレーションを持つ', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create(['user_id' => $user->id]);

    expect($user->workerProfile)->toBeInstanceOf(WorkerProfile::class)
        ->and($user->workerProfile->id)->toBe($profile->id);
});
