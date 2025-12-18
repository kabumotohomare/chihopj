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

    // 都道府県・市区町村のテストデータ作成
    $prefecture = Location::factory()->create([
        'code' => '13000',
        'prefecture' => '東京都',
        'city' => null,
    ]);

    $city = Location::factory()->create([
        'code' => '13101',
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    $response = Volt::actingAs($user)->test('company.register')
        ->set('prefecture', '東京都')
        ->set('location_id', $city->id)
        ->set('address', '丸の内1-1-1')
        ->set('representative', '山田太郎')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('company.profile'));

    // 企業プロフィールが作成されたか確認
    assertDatabaseHas('company_profiles', [
        'user_id' => $user->id,
        'location_id' => $city->id,
        'address' => '丸の内1-1-1',
        'representative' => '山田太郎',
        'phone_number' => '03-1234-5678',
    ]);
});

test('アイコン画像付きで企業登録が成功する', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'company']);

    $prefecture = Location::factory()->create([
        'code' => '13000',
        'prefecture' => '東京都',
        'city' => null,
    ]);

    $city = Location::factory()->create([
        'code' => '13101',
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    $icon = UploadedFile::fake()->image('icon.jpg', 200, 200);

    $response = Volt::actingAs($user)->test('company.register')
        ->set('icon', $icon)
        ->set('prefecture', '東京都')
        ->set('location_id', $city->id)
        ->set('address', '丸の内1-1-1')
        ->set('representative', '山田太郎')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('company.profile'));

    // アイコンが保存されたか確認
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

test('バリデーションエラー：所在地が未選択', function () {
    $user = User::factory()->create(['role' => 'company']);

    Volt::actingAs($user)->test('company.register')
        ->set('location_id', null)
        ->set('address', '丸の内1-1-1')
        ->set('representative', '山田太郎')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasErrors(['location_id' => 'required']);
});

test('バリデーションエラー：所在地住所が未入力', function () {
    $user = User::factory()->create(['role' => 'company']);

    $city = Location::factory()->create([
        'code' => '13101',
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    Volt::actingAs($user)->test('company.register')
        ->set('location_id', $city->id)
        ->set('address', '')
        ->set('representative', '山田太郎')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasErrors(['address' => 'required']);
});

test('バリデーションエラー：担当者名が未入力', function () {
    $user = User::factory()->create(['role' => 'company']);

    $city = Location::factory()->create([
        'code' => '13101',
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    Volt::actingAs($user)->test('company.register')
        ->set('location_id', $city->id)
        ->set('address', '丸の内1-1-1')
        ->set('representative', '')
        ->set('phone_number', '03-1234-5678')
        ->call('register')
        ->assertHasErrors(['representative' => 'required']);
});

test('バリデーションエラー：電話番号が未入力', function () {
    $user = User::factory()->create(['role' => 'company']);

    $city = Location::factory()->create([
        'code' => '13101',
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    Volt::actingAs($user)->test('company.register')
        ->set('location_id', $city->id)
        ->set('address', '丸の内1-1-1')
        ->set('representative', '山田太郎')
        ->set('phone_number', '')
        ->call('register')
        ->assertHasErrors(['phone_number' => 'required']);
});

test('都道府県選択で市区町村リストが更新される', function () {
    $user = User::factory()->create(['role' => 'company']);

    $prefecture = Location::factory()->create([
        'code' => '13000',
        'prefecture' => '東京都',
        'city' => null,
    ]);

    $city1 = Location::factory()->create([
        'code' => '13101',
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    $city2 = Location::factory()->create([
        'code' => '13102',
        'prefecture' => '東京都',
        'city' => '中央区',
    ]);

    $response = Volt::actingAs($user)->test('company.register')
        ->set('prefecture', '東京都')
        ->assertSet('location_id', null);

    // cities配列に都道府県の市区町村が含まれているか確認
    expect($response->get('cities'))->toHaveCount(2);
});

test('都道府県変更で市区町村選択がリセットされる', function () {
    $user = User::factory()->create(['role' => 'company']);

    $tokyo = Location::factory()->create([
        'code' => '13000',
        'prefecture' => '東京都',
        'city' => null,
    ]);

    $chiyoda = Location::factory()->create([
        'code' => '13101',
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);

    $osaka = Location::factory()->create([
        'code' => '27000',
        'prefecture' => '大阪府',
        'city' => null,
    ]);

    $response = Volt::actingAs($user)->test('company.register')
        ->set('prefecture', '東京都')
        ->set('location_id', $chiyoda->id)
        ->set('prefecture', '大阪府')
        ->assertSet('location_id', null);
});
