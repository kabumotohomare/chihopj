<?php

declare(strict_types=1);

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'municipal', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'worker', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
});

test('ユーザー作成時にアクティビティログが記録される', function () {
    $user = User::create([
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => 'password',
        'role' => 'worker',
    ]);

    $log = Activity::query()
        ->where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->where('event', 'created')
        ->first();

    expect($log)->not->toBeNull();
});

test('ユーザー更新時にアクティビティログが記録される', function () {
    $user = User::create([
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => 'password',
        'role' => 'worker',
    ]);

    $user->update(['name' => '更新済みユーザー']);

    $log = Activity::query()
        ->where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->properties['old']['name'])->toBe('テストユーザー');
    expect($log->properties['attributes']['name'])->toBe('更新済みユーザー');
});

test('操作者（causer）が記録される', function () {
    $admin = User::create([
        'name' => '管理者',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
    ]);

    // 管理者として操作を実行
    auth()->login($admin);

    $worker = User::create([
        'name' => 'ワーカー',
        'email' => 'worker@example.com',
        'password' => 'password',
        'role' => 'worker',
    ]);

    $log = Activity::query()
        ->where('subject_type', User::class)
        ->where('subject_id', $worker->id)
        ->where('event', 'created')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->causer_id)->toBe($admin->id);
    expect($log->causer_type)->toBe(User::class);
});

test('JobPost 作成時にアクティビティログが記録される', function () {
    $company = User::create([
        'name' => '企業ユーザー',
        'email' => 'company@example.com',
        'password' => 'password',
        'role' => 'company',
    ]);

    auth()->login($company);

    $jobPost = JobPost::create([
        'company_id' => $company->id,
        'job_title' => 'テスト求人',
        'purpose' => 'want_to_do',
        'location' => '東京',
        'job_detail' => '詳細',
        'posted_at' => now(),
    ]);

    $log = Activity::query()
        ->where('subject_type', JobPost::class)
        ->where('subject_id', $jobPost->id)
        ->where('event', 'created')
        ->first();

    expect($log)->not->toBeNull();
});

test('管理者がログ一覧ページにアクセスできる', function () {
    $admin = User::create([
        'name' => '管理者',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('super_admin');

    $this->actingAs($admin, 'admin')
        ->get('/admin/activity-logs')
        ->assertOk();
});

test('役所ユーザーがログ一覧ページにアクセスできる', function () {
    $municipal = User::create([
        'name' => '役所',
        'email' => 'municipal@example.com',
        'password' => 'password',
        'role' => 'municipal',
        'email_verified_at' => now(),
    ]);
    $municipal->assignRole('municipal');

    $this->actingAs($municipal, 'municipal')
        ->get('/municipal/activity-logs')
        ->assertOk();
});

test('一般ユーザーは管理パネルのログにアクセスできない', function () {
    $worker = User::create([
        'name' => 'ワーカー',
        'email' => 'worker@example.com',
        'password' => 'password',
        'role' => 'worker',
        'email_verified_at' => now(),
    ]);
    $worker->assignRole('worker');

    $this->actingAs($worker, 'admin')
        ->get('/admin/activity-logs')
        ->assertForbidden();
});
