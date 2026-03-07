<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'municipal', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'worker', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
});

test('管理者がユーザー一覧にアクセスできる', function () {
    $admin = User::create([
        'name' => '管理者',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin, 'admin')
        ->get('/admin/users')
        ->assertOk();
});

test('管理者がユーザー作成ページにアクセスできる', function () {
    $admin = User::create([
        'name' => '管理者',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin, 'admin')
        ->get('/admin/users/create')
        ->assertOk();
});

test('管理者がユーザーを閲覧できる', function () {
    $admin = User::create([
        'name' => '管理者',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('super_admin');

    $target = User::create([
        'name' => 'ターゲット',
        'email' => 'target@example.com',
        'password' => 'password',
        'role' => 'worker',
    ]);

    $this->actingAs($admin, 'admin')
        ->get("/admin/users/{$target->id}")
        ->assertOk();
});

test('一般ユーザーはユーザー管理にアクセスできない', function () {
    $worker = User::create([
        'name' => 'ワーカー',
        'email' => 'worker@example.com',
        'password' => 'password',
        'role' => 'worker',
        'email_verified_at' => now(),
    ]);
    $worker->assignRole('worker');

    $this->actingAs($worker, 'admin')
        ->get('/admin/users')
        ->assertForbidden();
});

test('UserPolicy: 管理者はユーザー CRUD が可能', function () {
    $admin = User::create([
        'name' => '管理者',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
    ]);
    $admin->assignRole('super_admin');

    $policy = new \App\Policies\UserPolicy;

    // super_admin は before() でバイパス
    expect($policy->before($admin, 'viewAny'))->toBeTrue();
    expect($policy->before($admin, 'create'))->toBeTrue();
    expect($policy->before($admin, 'update'))->toBeTrue();
    expect($policy->before($admin, 'delete'))->toBeTrue();
});

test('UserPolicy: 管理者は自分自身を削除できない', function () {
    $admin = User::create([
        'name' => '管理者',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
    ]);
    // super_admin ではないadminユーザーで検証
    $policy = new \App\Policies\UserPolicy;
    expect($policy->delete($admin, $admin))->toBeFalse();
});

test('UserPolicy: ワーカーはユーザー管理にアクセスできない', function () {
    $worker = User::create([
        'name' => 'ワーカー',
        'email' => 'worker@example.com',
        'password' => 'password',
        'role' => 'worker',
    ]);
    $worker->assignRole('worker');

    $policy = new \App\Policies\UserPolicy;

    // before() が null を返す（バイパスしない）
    expect($policy->before($worker, 'viewAny'))->toBeNull();
    // 各メソッドが false を返す
    expect($policy->viewAny($worker))->toBeFalse();
    expect($policy->create($worker))->toBeFalse();
});
