<?php

declare(strict_types=1);

use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

/**
 * 各テスト実行前にシードする
 */
beforeEach(function () {
    $this->seed(\Database\Seeders\CodeSeeder::class);
});

/**
 * 応募画面の表示テスト
 */
test('worker can view application form', function () {
    $worker = User::factory()->worker()->create();
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($worker);

    $response = get(route('jobs.apply', $jobPost));

    $response->assertOk();
    $response->assertSee($jobPost->job_title);
    $response->assertSee('応募する');
});

/**
 * 企業ユーザーは応募画面にアクセスできないテスト
 */
test('company user cannot view application form', function () {
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($company);

    $response = get(route('jobs.apply', $jobPost));

    $response->assertForbidden();
});

/**
 * 未認証ユーザーは応募画面にアクセスできないテスト
 */
test('guest cannot view application form', function () {
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    $response = get(route('jobs.apply', $jobPost));

    $response->assertRedirect(route('login'));
});

/**
 * ワーカーが応募できるテスト
 */
test('worker can apply to job post', function () {
    $worker = User::factory()->worker()->create();
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($worker);

    Volt::test('jobs.apply', ['jobPost' => $jobPost])
        ->set('reasons', ['near_hometown', 'empathize_with_goal'])
        ->set('motive', 'よろしくお願いします。')
        ->call('submit')
        ->assertRedirect(route('jobs.show', $jobPost));

    assertDatabaseHas('job_applications', [
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'motive' => 'よろしくお願いします。',
        'status' => 'applied',
    ]);

    $application = JobApplication::query()
        ->where('job_id', $jobPost->id)
        ->where('worker_id', $worker->id)
        ->first();

    expect($application->reasons)->toBe(['near_hometown', 'empathize_with_goal']);
    expect(session('status'))->toBe('応募が完了しました。');
});

/**
 * メッセージなしで応募できるテスト
 */
test('worker can apply without message', function () {
    $worker = User::factory()->worker()->create();
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($worker);

    Volt::test('jobs.apply', ['jobPost' => $jobPost])
        ->set('reasons', [])
        ->set('motive', '')
        ->call('submit')
        ->assertRedirect(route('jobs.show', $jobPost));

    assertDatabaseHas('job_applications', [
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'motive' => null,
        'status' => 'applied',
    ]);

    $application = JobApplication::query()
        ->where('job_id', $jobPost->id)
        ->where('worker_id', $worker->id)
        ->first();

    expect($application->reasons)->toBeNull();
});

/**
 * 1000文字を超えるメッセージはバリデーションエラーになるテスト
 */
test('application message cannot exceed 1000 characters', function () {
    $worker = User::factory()->worker()->create();
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($worker);

    Volt::test('jobs.apply', ['jobPost' => $jobPost])
        ->set('motive', str_repeat('あ', 1001))
        ->call('submit')
        ->assertHasErrors(['motive']);
});

/**
 * 重複応募はできないテスト（ポリシーで拒否）
 */
test('worker cannot apply to same job post twice', function () {
    $worker = User::factory()->worker()->create();
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    // 1回目の応募
    JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    actingAs($worker);

    // 2回目の応募はポリシーで拒否される（403エラー）
    $response = get(route('jobs.apply', $jobPost));

    $response->assertForbidden();
});

/**
 * 企業ユーザーは応募できないテスト
 */
test('company user cannot apply to job post', function () {
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($company);

    // 企業ユーザーは応募画面にアクセスできない（roleミドルウェアで拒否）
    $response = get(route('jobs.apply', $jobPost));

    $response->assertForbidden();
});

/**
 * 応募日時が自動設定されるテスト
 */
test('applied_at is automatically set', function () {
    $worker = User::factory()->worker()->create();
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($worker);

    Volt::test('jobs.apply', ['jobPost' => $jobPost])
        ->set('motive', 'よろしくお願いします。')
        ->call('submit');

    $application = JobApplication::query()
        ->where('job_id', $jobPost->id)
        ->where('worker_id', $worker->id)
        ->first();

    expect($application)->not->toBeNull();
    expect($application->applied_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

/**
 * 応募理由が複数選択できるテスト
 */
test('worker can select multiple reasons', function () {
    $worker = User::factory()->worker()->create();
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($worker);

    Volt::test('jobs.apply', ['jobPost' => $jobPost])
        ->set('reasons', ['near_hometown', 'lived_before', 'wanted_to_visit'])
        ->set('motive', 'テストメッセージ')
        ->call('submit')
        ->assertRedirect(route('jobs.show', $jobPost));

    $application = JobApplication::query()
        ->where('job_id', $jobPost->id)
        ->where('worker_id', $worker->id)
        ->first();

    expect($application->reasons)->toBe(['near_hometown', 'lived_before', 'wanted_to_visit']);
});

/**
 * 無効な応募理由はバリデーションエラーになるテスト
 */
test('invalid reason causes validation error', function () {
    $worker = User::factory()->worker()->create();
    $company = User::factory()->company()->create();
    $jobPost = JobPost::factory()->create(['company_id' => $company->id]);

    actingAs($worker);

    Volt::test('jobs.apply', ['jobPost' => $jobPost])
        ->set('reasons', ['invalid_reason'])
        ->set('motive', 'テストメッセージ')
        ->call('submit')
        ->assertHasErrors(['reasons.0']);
});
