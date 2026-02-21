<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

use function Pest\Laravel\assertDatabaseHas;

test('企業登録画面が認証済みユーザーに表示される', function () {
    $user = User::factory()->create(['role' => 'company']);

    $response = $this->actingAs($user)->get(route('company.register'));

    $response->assertOk()
        ->assertSeeLivewire('company.register');
});

test('未認証ユーザーは企業登録画面にアクセスできない', function () {
    $response = $this->get(route('company.register'));

    $response->assertRedirect(route('login'));
});

test('企業登録が成功する', function () {
    $user = User::factory()->create(['role' => 'company']);

    // 平泉町のlocation_idを取得
    $hiraizumiLocation = Location::factory()->create([
        'code' => '034029',
        'prefecture' => '岩手県',
        'city' => '西磐井郡平泉町',
    ]);

    $response = Volt::actingAs($user)->test('company.register')
        ->set('name', '株式会社テスト')
        ->set('address', '平泉字泉屋1-1')
        ->set('representative', '山田太郎')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('company.profile'));

    // usersテーブルのnameが更新されたか確認
    $user->refresh();
    expect($user->name)->toBe('株式会社テスト');

    // 企業プロフィールが作成されたか確認
    assertDatabaseHas('company_profiles', [
        'user_id' => $user->id,
        'location_id' => $hiraizumiLocation->id,
        'address' => '平泉字泉屋1-1',
        'representative' => '山田太郎',
        'phone_number' => '03-1234-5678',
    ]);
});

test('アイコン画像付きで企業登録が成功する', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'company']);

    // 平泉町のlocation_idを取得
    $hiraizumiLocation = Location::factory()->create([
        'code' => '034029',
        'prefecture' => '岩手県',
        'city' => '西磐井郡平泉町',
    ]);

    $icon = UploadedFile::fake()->image('icon.jpg', 200, 200);

    $response = Volt::actingAs($user)->test('company.register')
        ->set('name', '株式会社テスト')
        ->set('icon', $icon)
        ->set('address', '平泉字泉屋1-1')
        ->set('representative', '山田太郎')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('company.profile'));

    // アイコンが保存されたか確認
    $user->refresh();
    $companyProfile = $user->companyProfile;
    expect($companyProfile->icon)->not->toBeNull();
    Storage::disk('public')->assertExists($companyProfile->icon);
});

test('既にプロフィールが登録されている場合は企業詳細画面にリダイレクト', function () {
    $user = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create([
        'code' => '13101',
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    CompanyProfile::factory()->create([
        'user_id' => $user->id,
        'location_id' => $location->id,
    ]);

    $response = $this->actingAs($user)->get(route('company.register'));

    $response->assertRedirect(route('company.profile'));
});

test('バリデーションエラー：団体・事業者名が未入力', function () {
    $user = User::factory()->create(['role' => 'company']);

    Volt::actingAs($user)->test('company.register')
        ->set('name', '')
        ->set('address', '平泉字泉屋1-1')
        ->set('representative', '山田太郎')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasErrors(['name' => 'required']);
});

test('バリデーションエラー：所在地住所が未入力', function () {
    $user = User::factory()->create(['role' => 'company']);

    Volt::actingAs($user)->test('company.register')
        ->set('name', '株式会社テスト')
        ->set('address', '')
        ->set('representative', '山田太郎')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasErrors(['address' => 'required']);
});

test('バリデーションエラー：担当者名が未入力', function () {
    $user = User::factory()->create(['role' => 'company']);

    Volt::actingAs($user)->test('company.register')
        ->set('name', '株式会社テスト')
        ->set('address', '平泉字泉屋1-1')
        ->set('representative', '')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasErrors(['representative' => 'required']);
});

test('バリデーションエラー：電話番号が未入力', function () {
    $user = User::factory()->create(['role' => 'company']);

    Volt::actingAs($user)->test('company.register')
        ->set('name', '株式会社テスト')
        ->set('address', '平泉字泉屋1-1')
        ->set('representative', '山田太郎')
        ->set('phone_number', '')
        ->call('register')
        ->assertHasErrors(['phone_number' => 'required']);
});
