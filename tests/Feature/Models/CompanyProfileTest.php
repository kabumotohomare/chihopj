<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use App\Models\User;

test('企業プロフィールを作成できる', function () {
    $user = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();

    $profile = CompanyProfile::factory()->create([
        'user_id' => $user->id,
        'location_id' => $location->id,
        'address' => '千代田1-1-1',
        'representative' => '山田太郎',
        'phone_number' => '03-1234-5678',
    ]);

    expect($profile->user_id)->toBe($user->id)
        ->and($profile->location_id)->toBe($location->id)
        ->and($profile->address)->toBe('千代田1-1-1')
        ->and($profile->representative)->toBe('山田太郎')
        ->and($profile->phone_number)->toBe('03-1234-5678');
});

test('企業プロフィールはユーザーとリレーションを持つ', function () {
    $user = User::factory()->create(['role' => 'company']);
    $profile = CompanyProfile::factory()->create(['user_id' => $user->id]);

    expect($profile->user)->toBeInstanceOf(User::class)
        ->and($profile->user->id)->toBe($user->id);
});

test('企業プロフィールは所在地とリレーションを持つ', function () {
    $location = Location::factory()->create();
    $profile = CompanyProfile::factory()->create(['location_id' => $location->id]);

    expect($profile->location)->toBeInstanceOf(Location::class)
        ->and($profile->location->id)->toBe($location->id);
});
