<?php

declare(strict_types=1);

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
    $response->assertSee('平泉町とのかかわりかた');
    $response->assertSee('ひらいず民');
    $response->assertSee('ホスト');
});

test('ワーカーとして登録できる', function () {
    $response = $this->post(route('register.store'), [
        'email' => 'worker@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'worker',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('worker.register'));
    $this->assertAuthenticated();

    $user = User::where('email', 'worker@example.com')->first();
    expect($user->role)->toBe('worker');
    expect($user->name)->toBeNull(); // 名前はプロフィール登録時に設定される
    expect($user->workerProfile)->toBeNull();
});

test('カンパニーとして登録できる', function () {
    $response = $this->post(route('register.store'), [
        'email' => 'company@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'company',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('company.register'));
    $this->assertAuthenticated();

    $user = User::where('email', 'company@example.com')->first();
    expect($user->role)->toBe('company');
    expect($user->name)->toBeNull(); // 名前はプロフィール登録時に設定される
    expect($user->companyProfile)->toBeNull();
});

test('ロールが未選択の場合はバリデーションエラーになる', function () {
    $response = $this->post(route('register.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['role']);
});

test('無効なロールの場合はバリデーションエラーになる', function () {
    $response = $this->post(route('register.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'invalid',
    ]);

    $response->assertSessionHasErrors(['role']);
});

test('ワーカー登録画面にアクセスできる', function () {
    $user = User::factory()->create(['role' => 'worker']);

    $response = $this->actingAs($user)->get(route('worker.register'));

    $response->assertSuccessful();
    $response->assertSeeLivewire('worker.register');
});

test('カンパニー登録画面にアクセスできる', function () {
    $user = User::factory()->create(['role' => 'company']);

    $response = $this->actingAs($user)->get(route('company.register'));

    $response->assertSuccessful();
    $response->assertSeeLivewire('company.register');
});
