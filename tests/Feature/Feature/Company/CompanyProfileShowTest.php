<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use App\Models\User;
use Livewire\Volt\Volt;

test('ゲストは企業プロフィール画面にアクセスできない', function () {
    $response = $this->get(route('company.profile'));

    $response->assertRedirect(route('login'));
});

test('ワーカーユーザーは企業プロフィール画面にアクセスできない', function () {
    $user = User::factory()->create(['role' => 'worker']);

    $this->actingAs($user);

    $response = $this->get(route('company.profile'));

    $response->assertForbidden();
});

test('企業ユーザーは自分のプロフィール画面にアクセスできる', function () {
    $user = User::factory()->create(['role' => 'company']);
    CompanyProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get(route('company.profile'));

    $response->assertOk();
});

test('企業プロフィール画面に企業名が表示される', function () {
    $user = User::factory()->create([
        'role' => 'company',
        'name' => 'テスト株式会社',
    ]);
    $profile = CompanyProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Volt::test('company.show')
        ->assertSee('テスト株式会社');
});

test('企業プロフィール画面に所在地が表示される', function () {
    $user = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);
    $profile = CompanyProfile::factory()->create([
        'user_id' => $user->id,
        'location_id' => $location->id,
    ]);

    $this->actingAs($user);

    Volt::test('company.show')
        ->assertSee('東京都 千代田区');
});

test('企業プロフィール画面に所在地住所が表示される', function () {
    $user = User::factory()->create(['role' => 'company']);
    $profile = CompanyProfile::factory()->create([
        'user_id' => $user->id,
        'address' => '千代田1-1-1 テストビル3F',
    ]);

    $this->actingAs($user);

    Volt::test('company.show')
        ->assertSee('千代田1-1-1 テストビル3F');
});

test('企業プロフィール画面に担当者名が表示される', function () {
    $user = User::factory()->create(['role' => 'company']);
    $profile = CompanyProfile::factory()->create([
        'user_id' => $user->id,
        'representative' => '山田太郎',
    ]);

    $this->actingAs($user);

    Volt::test('company.show')
        ->assertSee('山田太郎');
});

test('企業プロフィール画面に担当者連絡先が表示される', function () {
    $user = User::factory()->create(['role' => 'company']);
    $profile = CompanyProfile::factory()->create([
        'user_id' => $user->id,
        'phone_number' => '03-1234-5678',
    ]);

    $this->actingAs($user);

    Volt::test('company.show')
        ->assertSee('03-1234-5678');
});

test('企業プロフィール画面に登録日時と更新日時が表示される', function () {
    $user = User::factory()->create(['role' => 'company']);
    $profile = CompanyProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get(route('company.profile'));

    $response->assertSee('登録日時')
        ->assertSee('更新日時');
});

test('企業プロフィールが存在しない場合は404エラーになる', function () {
    $user = User::factory()->create(['role' => 'company']);

    $this->actingAs($user);

    $response = $this->get(route('company.profile'));

    $response->assertNotFound();
});
