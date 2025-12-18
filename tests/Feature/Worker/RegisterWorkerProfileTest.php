<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

test('ワーカープロフィール登録画面にアクセスできる', function () {
    $user = User::factory()->create(['role' => 'worker']);

    $this->actingAs($user);

    $response = $this->get(route('worker.register'));

    $response->assertOk();
});

test('有効な画像をアップロードできる', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('icon.jpg', 500, 500)->size(1024); // 1MB

    Volt::test('worker.register')
        ->set('handle_name', 'テストユーザー')
        ->set('icon', $file)
        ->set('gender', 'male')
        ->set('birth_year', '1990')
        ->set('birth_month', '5')
        ->set('birth_day', '15')
        ->set('birth_location_id', $location->id)
        ->set('current_location_1_id', $location->id)
        ->call('register')
        ->assertHasNoErrors();

    Storage::disk('public')->assertExists('icons/'.$file->hashName());

    $profile = WorkerProfile::where('user_id', $user->id)->first();
    expect($profile->icon)->not->toBeNull();
});

test('画像なしでも登録できる', function () {
    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create();

    $this->actingAs($user);

    Volt::test('worker.register')
        ->set('handle_name', 'テストユーザー')
        ->set('icon', null)
        ->set('gender', 'male')
        ->set('birth_year', '1990')
        ->set('birth_month', '5')
        ->set('birth_day', '15')
        ->set('birth_location_id', $location->id)
        ->set('current_location_1_id', $location->id)
        ->call('register')
        ->assertHasNoErrors();

    $profile = WorkerProfile::where('user_id', $user->id)->first();
    expect($profile->icon)->toBeNull();
});

test('2MBを超える画像はバリデーションエラーになる', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('icon.jpg', 500, 500)->size(3000); // 3MB

    Volt::test('worker.register')
        ->set('handle_name', 'テストユーザー')
        ->set('icon', $file)
        ->set('gender', 'male')
        ->set('birth_year', '1990')
        ->set('birth_month', '5')
        ->set('birth_day', '15')
        ->set('birth_location_id', $location->id)
        ->set('current_location_1_id', $location->id)
        ->call('register')
        ->assertHasErrors(['icon']);
});

test('小さすぎる画像はバリデーションエラーになる', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('icon.jpg', 50, 50); // 50x50px

    Volt::test('worker.register')
        ->set('handle_name', 'テストユーザー')
        ->set('icon', $file)
        ->set('gender', 'male')
        ->set('birth_year', '1990')
        ->set('birth_month', '5')
        ->set('birth_day', '15')
        ->set('birth_location_id', $location->id)
        ->set('current_location_1_id', $location->id)
        ->call('register')
        ->assertHasErrors(['icon']);
});

test('大きすぎる画像はバリデーションエラーになる', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('icon.jpg', 3000, 3000); // 3000x3000px

    Volt::test('worker.register')
        ->set('handle_name', 'テストユーザー')
        ->set('icon', $file)
        ->set('gender', 'male')
        ->set('birth_year', '1990')
        ->set('birth_month', '5')
        ->set('birth_day', '15')
        ->set('birth_location_id', $location->id)
        ->set('current_location_1_id', $location->id)
        ->call('register')
        ->assertHasErrors(['icon']);
});

test('画像以外のファイルはバリデーションエラーになる', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 100);

    Volt::test('worker.register')
        ->set('handle_name', 'テストユーザー')
        ->set('icon', $file)
        ->set('gender', 'male')
        ->set('birth_year', '1990')
        ->set('birth_month', '5')
        ->set('birth_day', '15')
        ->set('birth_location_id', $location->id)
        ->set('current_location_1_id', $location->id)
        ->call('register')
        ->assertHasErrors(['icon']);
});

test('許可されていない画像形式はバリデーションエラーになる', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create();

    $this->actingAs($user);

    // BMPファイルを作成（許可されていない形式）
    $file = UploadedFile::fake()->create('icon.bmp', 100, 'image/bmp');

    Volt::test('worker.register')
        ->set('handle_name', 'テストユーザー')
        ->set('icon', $file)
        ->set('gender', 'male')
        ->set('birth_year', '1990')
        ->set('birth_month', '5')
        ->set('birth_day', '15')
        ->set('birth_location_id', $location->id)
        ->set('current_location_1_id', $location->id)
        ->call('register')
        ->assertHasErrors(['icon']);
});

test('正方形の画像が推奨される', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'worker']);
    $location = Location::factory()->create();

    $this->actingAs($user);

    // 正方形の画像
    $file = UploadedFile::fake()->image('icon.jpg', 400, 400);

    Volt::test('worker.register')
        ->set('handle_name', 'テストユーザー')
        ->set('icon', $file)
        ->set('gender', 'male')
        ->set('birth_year', '1990')
        ->set('birth_month', '5')
        ->set('birth_day', '15')
        ->set('birth_location_id', $location->id)
        ->set('current_location_1_id', $location->id)
        ->call('register')
        ->assertHasNoErrors();

    $profile = WorkerProfile::where('user_id', $user->id)->first();
    expect($profile->icon)->not->toBeNull();
});
