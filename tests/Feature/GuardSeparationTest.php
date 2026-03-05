<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin ガードでログインしても web ガードはログアウト状態', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    auth()->guard('admin')->login($user);

    expect(auth()->guard('admin')->check())->toBeTrue();
    expect(auth()->guard('web')->check())->toBeFalse();
});

test('government ガードでログインしても web ガードはログアウト状態', function () {
    $user = User::create([
        'name' => '役所ユーザー',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
        'email_verified_at' => now(),
    ]);

    auth()->guard('government')->login($user);

    expect(auth()->guard('government')->check())->toBeTrue();
    expect(auth()->guard('web')->check())->toBeFalse();
});

test('web ガードでログインしても admin ガードはログアウト状態', function () {
    $user = User::create([
        'name' => 'Worker',
        'email' => 'worker@example.com',
        'password' => 'password',
        'role' => 'worker',
        'email_verified_at' => now(),
    ]);

    auth()->guard('web')->login($user);

    expect(auth()->guard('web')->check())->toBeTrue();
    expect(auth()->guard('admin')->check())->toBeFalse();
    expect(auth()->guard('government')->check())->toBeFalse();
});

test('admin と government は互いに独立している', function () {
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    $municipal = User::create([
        'name' => '役所ユーザー',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
        'email_verified_at' => now(),
    ]);

    auth()->guard('admin')->login($admin);

    expect(auth()->guard('admin')->check())->toBeTrue();
    expect(auth()->guard('government')->check())->toBeFalse();

    auth()->guard('government')->login($municipal);

    expect(auth()->guard('admin')->check())->toBeTrue();
    expect(auth()->guard('government')->check())->toBeTrue();
    expect(auth()->guard('web')->check())->toBeFalse();
});

test('admin ガードのログイン状態は web ガードの認証に影響しない', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    // admin ガードでログイン
    auth()->guard('admin')->login($user);

    // web ガードの認証チェックに影響しない
    expect(auth()->guard('admin')->check())->toBeTrue();
    expect(auth()->guard('admin')->user()->id)->toBe($user->id);
    expect(auth()->guard('web')->check())->toBeFalse();
    expect(auth()->guard('web')->user())->toBeNull();
});
