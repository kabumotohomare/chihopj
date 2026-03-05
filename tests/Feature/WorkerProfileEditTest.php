<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // テストユーザーとワーカープロフィールを作成
    $this->user = User::factory()->create(['role' => 'worker']);
    $this->profile = WorkerProfile::factory()->create(['user_id' => $this->user->id]);

    // 地域データの準備
    $this->prefecture = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => null,
    ]);
    $this->city = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);
});

test('ワーカーが自分のプロフィール編集画面にアクセスできる', function () {
    actingAs($this->user)
        ->get(route('worker.edit'))
        ->assertOk()
        ->assertSeeLivewire('worker.edit');
});

test('認証されていないユーザーはワーカープロフィール編集画面にアクセスできない', function () {
    $this->get(route('worker.edit'))
        ->assertRedirect(route('login'));
});

test('企業ユーザーはワーカープロフィール編集画面にアクセスできない', function () {
    $companyUser = User::factory()->create(['role' => 'company']);

    actingAs($companyUser)
        ->get(route('worker.edit'))
        ->assertForbidden();
});

test('ワーカープロフィールを更新できる', function () {
    $newCity = Location::factory()->create([
        'prefecture' => '大阪府',
        'city' => '大阪市',
    ]);

    Volt::actingAs($this->user)
        ->test('worker.edit')
        ->set('handle_name', '更新後のハンドルネーム')
        ->set('gender', 'female')
        ->set('birthYear', 1990)
        ->set('birthMonth', 5)
        ->set('birthDay', 15)
        ->set('message', 'よろしくお願いします')
        ->set('current_location_1_id', $newCity->id)
        ->set('current_address', 'テスト住所1-1-1')
        ->set('phone_number', '090-1234-5678')
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('worker.profile'));

    // データベースが更新されたことを確認
    $this->assertDatabaseHas('worker_profiles', [
        'user_id' => $this->user->id,
        'handle_name' => '更新後のハンドルネーム',
        'gender' => 'female',
        'birthdate' => '1990-05-15',
        'message' => 'よろしくお願いします',
        'current_location_1_id' => $newCity->id,
        'current_address' => 'テスト住所1-1-1',
        'phone_number' => '090-1234-5678',
    ]);
});

test('必須項目が空の場合はバリデーションエラーが発生する', function () {
    Volt::actingAs($this->user)
        ->test('worker.edit')
        ->set('handle_name', '')
        ->set('gender', '')
        ->set('birthYear', null)
        ->set('birthMonth', null)
        ->set('birthDay', null)
        ->set('current_location_1_id', null)
        ->set('current_address', '')
        ->set('phone_number', '')
        ->call('update')
        ->assertHasErrors([
            'handle_name',
            'gender',
            'birthYear',
            'birthMonth',
            'birthDay',
            'current_location_1_id',
            'current_address',
            'phone_number',
        ]);
});

test('ハンドルネームが50文字を超える場合はバリデーションエラーが発生する', function () {
    Volt::actingAs($this->user)
        ->test('worker.edit')
        ->set('handle_name', str_repeat('あ', 51))
        ->call('update')
        ->assertHasErrors(['handle_name']);
});

test('性別が不正な値の場合はバリデーションエラーが発生する', function () {
    Volt::actingAs($this->user)
        ->test('worker.edit')
        ->set('gender', 'invalid')
        ->call('update')
        ->assertHasErrors(['gender']);
});

test('ひとことメッセージが200文字を超える場合はバリデーションエラーが発生する', function () {
    Volt::actingAs($this->user)
        ->test('worker.edit')
        ->set('message', str_repeat('あ', 201))
        ->call('update')
        ->assertHasErrors(['message']);
});

test('任意項目を空にして更新できる', function () {
    Volt::actingAs($this->user)
        ->test('worker.edit')
        ->set('handle_name', 'テストユーザー')
        ->set('gender', 'male')
        ->set('birthYear', 1990)
        ->set('birthMonth', 1)
        ->set('birthDay', 1)
        ->set('current_location_1_id', $this->city->id)
        ->set('current_address', 'テスト住所1-1-1')
        ->set('phone_number', '090-1234-5678')
        ->set('message', '')
        ->set('current_location_2_id', null)
        ->call('update')
        ->assertHasNoErrors();

    // データベースに反映されたことを確認
    $this->assertDatabaseHas('worker_profiles', [
        'user_id' => $this->user->id,
        'message' => null,
        'current_location_2_id' => null,
    ]);
});

test('編集画面に既存データが初期表示される', function () {
    Volt::actingAs($this->user)
        ->test('worker.edit')
        ->assertSet('handle_name', $this->profile->handle_name)
        ->assertSet('gender', $this->profile->gender)
        ->assertSet('birthYear', (int) $this->profile->birthdate->format('Y'))
        ->assertSet('birthMonth', (int) $this->profile->birthdate->format('m'))
        ->assertSet('birthDay', (int) $this->profile->birthdate->format('d'))
        ->assertSet('current_location_1_id', $this->profile->current_location_1_id);
});
