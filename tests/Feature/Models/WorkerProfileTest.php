<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;

test('ワーカープロフィールを作成できる', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $birthLocation = Location::factory()->create();
    $currentLocation = Location::factory()->create();

    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'handle_name' => 'テストユーザー',
        'gender' => 'male',
        'birthdate' => '1990-05-15',
        'birth_location_id' => $birthLocation->id,
        'current_location_1_id' => $currentLocation->id,
    ]);

    expect($profile->user_id)->toBe($user->id)
        ->and($profile->handle_name)->toBe('テストユーザー')
        ->and($profile->gender)->toBe('male')
        ->and($profile->birthdate->format('Y-m-d'))->toBe('1990-05-15');
});

test('ワーカープロフィールはユーザーとリレーションを持つ', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create(['user_id' => $user->id]);

    expect($profile->user)->toBeInstanceOf(User::class)
        ->and($profile->user->id)->toBe($user->id);
});

test('年齢を正しく計算できる', function () {
    $birthdate = now()->subYears(30)->format('Y-m-d');
    $profile = WorkerProfile::factory()->create([
        'birthdate' => $birthdate,
    ]);

    expect($profile->age)->toBe(30);
});

test('性別の日本語ラベルを取得できる', function () {
    $male = WorkerProfile::factory()->create(['gender' => 'male']);
    $female = WorkerProfile::factory()->create(['gender' => 'female']);
    $other = WorkerProfile::factory()->create(['gender' => 'other']);

    expect($male->gender_label)->toBe('男性')
        ->and($female->gender_label)->toBe('女性')
        ->and($other->gender_label)->toBe('その他');
});

test('興味のあるお手伝いの日本語ラベルを取得できる', function () {
    $profile = WorkerProfile::factory()->create([
        'available_action' => ['mowing', 'snowplow', 'diy'],
    ]);

    $labels = $profile->available_action_labels;

    expect($labels)->toBeArray()
        ->and($labels)->toContain('草刈り')
        ->and($labels)->toContain('雪かき')
        ->and($labels)->toContain('DIY');
});

test('ワーカープロフィールは複数の地域とリレーションを持つ', function () {
    $birthLocation = Location::factory()->create();
    $currentLocation1 = Location::factory()->create();
    $currentLocation2 = Location::factory()->create();
    $favoriteLocation1 = Location::factory()->create();

    $profile = WorkerProfile::factory()->create([
        'birth_location_id' => $birthLocation->id,
        'current_location_1_id' => $currentLocation1->id,
        'current_location_2_id' => $currentLocation2->id,
        'favorite_location_1_id' => $favoriteLocation1->id,
    ]);

    expect($profile->birthLocation)->toBeInstanceOf(Location::class)
        ->and($profile->currentLocation1)->toBeInstanceOf(Location::class)
        ->and($profile->currentLocation2)->toBeInstanceOf(Location::class)
        ->and($profile->favoriteLocation1)->toBeInstanceOf(Location::class);
});
