<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Shield ロールを作成
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'municipal', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'worker', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
});

test('super_admin ロールを持つユーザーは admin パネルにアクセスできる', function () {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    $user->assignRole('super_admin');

    expect($user->hasRole('super_admin'))->toBeTrue();
    expect($user->canAccessPanel(filament()->getPanel('admin')))->toBeTrue();
});

test('municipal ロールを持つユーザーは municipal パネルにアクセスできる', function () {
    $user = User::create([
        'name' => '役所ユーザー',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
        'email_verified_at' => now(),
    ]);
    $user->assignRole('municipal');

    expect($user->hasRole('municipal'))->toBeTrue();
    expect($user->canAccessPanel(filament()->getPanel('municipal')))->toBeTrue();
});

test('worker ロールのユーザーは admin パネルにアクセスできない', function () {
    $user = User::create([
        'name' => 'Worker',
        'email' => 'worker@example.com',
        'password' => 'password',
        'role' => 'worker',
        'email_verified_at' => now(),
    ]);
    $user->assignRole('worker');

    expect($user->canAccessPanel(filament()->getPanel('admin')))->toBeFalse();
    expect($user->canAccessPanel(filament()->getPanel('municipal')))->toBeFalse();
});

test('super_admin は Policy の before で全操作がバイパスされる', function () {
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('super_admin');

    $policy = new \App\Policies\JobApplicationPolicy;

    // before() が true を返すことを確認
    expect($policy->before($admin, 'viewAny'))->toBeTrue();
    expect($policy->before($admin, 'create'))->toBeTrue();
    expect($policy->before($admin, 'delete'))->toBeTrue();
});

test('worker ユーザーは Policy の before でバイパスされない', function () {
    $worker = User::create([
        'name' => 'Worker',
        'email' => 'worker@example.com',
        'password' => 'password',
        'role' => 'worker',
        'email_verified_at' => now(),
    ]);
    $worker->assignRole('worker');

    $policy = new \App\Policies\JobApplicationPolicy;

    // before() が null を返し、通常の認可ロジックに委譲される
    expect($policy->before($worker, 'viewAny'))->toBeNull();
});

test('HasRoles トレイトが User モデルに正しく組み込まれている', function () {
    $user = User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password',
        'role' => 'worker',
        'email_verified_at' => now(),
    ]);

    // assignRole / hasRole が動作することを確認
    $user->assignRole('worker');
    expect($user->hasRole('worker'))->toBeTrue();
    expect($user->hasRole('super_admin'))->toBeFalse();
});

test('ユーザー登録時に Spatie ロールが割り当てられる', function () {
    $createNewUser = new \App\Actions\Fortify\CreateNewUser;

    $user = $createNewUser->create([
        'email' => 'newworker@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'worker',
    ]);

    expect($user->hasRole('worker'))->toBeTrue();
});
