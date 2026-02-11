<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

test('ゲストはワーカープロフィール画面にアクセスできない', function () {
    $response = $this->get(route('worker.profile'));

    $response->assertRedirect(route('login'));
});

test('企業ユーザーはワーカープロフィール画面にアクセスできない', function () {
    $user = User::factory()->create(['role' => 'company']);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));

    $response->assertForbidden();
});

test('ワーカーユーザーは自分のプロフィール画面にアクセスできる', function () {
    $user = User::factory()->create(['role' => 'worker']);
    WorkerProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));

    $response->assertOk();
});

test('ワーカープロフィール画面にハンドルネームが表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'handle_name' => 'テストハンドルネーム',
    ]);

    $this->actingAs($user);

    Volt::test('worker.show')
        ->assertSee('テストハンドルネーム');
});

test('ワーカープロフィール画面に性別が日本語で表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'gender' => 'male',
    ]);

    $this->actingAs($user);

    Volt::test('worker.show')
        ->assertSee('男性');
});

test('ワーカープロフィール画面に生年月日と年齢が表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'birthdate' => '1990-05-15',
    ]);

    $this->actingAs($user);

    Volt::test('worker.show')
        ->assertSee('1990年5月15日')
        ->assertSee('歳');
});

test('ワーカープロフィール画面に出身地が表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'birth_location_id' => $location->id,
    ]);

    $this->actingAs($user);

    Volt::test('worker.show')
        ->assertSee('東京都 千代田区');
});

test('ワーカープロフィール画面にひとことメッセージが表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'message' => 'よろしくお願いします',
    ]);

    $this->actingAs($user);

    Volt::test('worker.show')
        ->assertSee('よろしくお願いします');
});

test('ワーカープロフィール画面に現在のお住まいが表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $location1 = Location::factory()->create([
        'prefecture' => '東京都',
        'city' => '千代田区',
    ]);
    $location2 = Location::factory()->create([
        'prefecture' => '神奈川県',
        'city' => '横浜市',
    ]);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'current_location_1_id' => $location1->id,
        'current_location_2_id' => $location2->id,
    ]);

    $this->actingAs($user);

    Volt::test('worker.show')
        ->assertSee('東京都 千代田区')
        ->assertSee('神奈川県 横浜市');
});

test('ワーカープロフィール画面に登録日時と更新日時が表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));

    $response->assertSee('登録日時')
        ->assertSee('更新日時');
});

test('地域が未設定の場合は「未設定」と表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'current_location_2_id' => null,
    ]);

    $this->actingAs($user);

    Volt::test('worker.show')
        ->assertSee('未設定');
});

test('ひとことメッセージが未設定の場合は「未設定」と表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'message' => null,
    ]);

    $this->actingAs($user);

    Volt::test('worker.show')
        ->assertSee('未設定');
});

test('アイコン画像が設定されている場合は画像が表示される', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'worker']);
    $file = UploadedFile::fake()->image('icon.jpg');
    $path = $file->store('icons', 'public');

    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'icon' => $path,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));

    $response->assertSee('/storage/'.$path, false);
});

test('アイコン画像が未設定の場合はデフォルトアイコンが表示される', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $profile = WorkerProfile::factory()->create([
        'user_id' => $user->id,
        'icon' => null,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('worker.profile'));

    // デフォルトアイコンのクラスが存在することを確認
    $response->assertSee('rounded-full bg-zinc-200 dark:bg-zinc-700', false);
});
