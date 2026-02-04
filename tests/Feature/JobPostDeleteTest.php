<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\CodeSeeder::class);
});

/**
 * 企業ユーザーは自社の募集を削除できる
 */
test('company user can delete their own job post without applications', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成（応募なし）
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // 認証
    $this->actingAs($company);

    // 削除実行
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertRedirect(route('jobs.index'));

    // 募集が削除されたことを確認
    expect(JobPost::find($jobPost->id))->toBeNull();
});

/**
 * 応募中のステータスの応募がある募集は削除できない
 */
test('company user cannot delete job post with applied applications', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);

    // 応募中の応募作成
    JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
        'status' => 'applied',
    ]);

    // 認証
    $this->actingAs($company);

    // 削除実行 - リダイレクトしないことを確認
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertNoRedirect();

    // 募集が削除されていないことを確認
    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * 他社の募集は削除できない
 */
test('company user cannot delete other company job post', function () {
    // 企業ユーザーA作成
    $companyA = User::factory()->create(['role' => 'company']);
    $locationA = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $companyA->id,
        'location_id' => $locationA->id,
    ]);

    // 企業ユーザーB作成
    $companyB = User::factory()->create(['role' => 'company']);
    $locationB = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $companyB->id,
        'location_id' => $locationB->id,
    ]);

    // 企業Bの募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $companyB->id,
    ]);

    // 企業Aで認証
    $this->actingAs($companyA);

    // 削除実行（403エラー）
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertForbidden();

    // 募集が削除されていないことを確認
    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * ワーカーユーザーは募集を削除できない
 */
test('worker user cannot delete job post', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);

    // 認証
    $this->actingAs($worker);

    // 削除実行（403エラー）
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertForbidden();

    // 募集が削除されていないことを確認
    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * 未認証ユーザーは募集を削除できない
 */
test('guest cannot delete job post', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // 未認証で削除実行（403エラー）
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertForbidden();

    // 募集が削除されていないことを確認
    expect(JobPost::find($jobPost->id))->not->toBeNull();
});

/**
 * 不承認済みの応募のみがある募集は削除できる
 */
test('company user can delete job post with rejected applications', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);

    // 不承認済みの応募作成
    JobApplication::factory()->rejected()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
    ]);

    // 認証
    $this->actingAs($company);

    // 削除実行
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertRedirect(route('jobs.index'));

    // 募集が削除されたことを確認
    expect(JobPost::find($jobPost->id))->toBeNull();
});

/**
 * 承認済みの応募のみがある募集は削除できる
 */
test('company user can delete job post with accepted applications', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);

    // 承認済みの応募作成
    JobApplication::factory()->accepted()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
    ]);

    // 認証
    $this->actingAs($company);

    // 削除実行
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertRedirect(route('jobs.index'));

    // 募集が削除されたことを確認
    expect(JobPost::find($jobPost->id))->toBeNull();
});

/**
 * 辞退済みの応募のみがある募集は削除できる
 */
test('company user can delete job post with declined applications', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker = User::factory()->create(['role' => 'worker']);

    // 辞退済みの応募作成
    JobApplication::factory()->declined()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker->id,
    ]);

    // 認証
    $this->actingAs($company);

    // 削除実行
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertRedirect(route('jobs.index'));

    // 募集が削除されたことを確認
    expect(JobPost::find($jobPost->id))->toBeNull();
});

/**
 * 応募中と不承認済みの応募が混在する場合は削除できない
 */
test('company user cannot delete job post with mixed applications', function () {
    // 企業ユーザー作成
    $company = User::factory()->create(['role' => 'company']);
    $location = Location::factory()->create();
    CompanyProfile::factory()->create([
        'user_id' => $company->id,
        'location_id' => $location->id,
    ]);

    // 募集作成
    $jobPost = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    // ワーカーユーザー作成
    $worker1 = User::factory()->create(['role' => 'worker']);
    $worker2 = User::factory()->create(['role' => 'worker']);

    // 応募中の応募作成
    JobApplication::factory()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker1->id,
        'status' => 'applied',
    ]);

    // 不承認済みの応募作成
    JobApplication::factory()->rejected()->create([
        'job_id' => $jobPost->id,
        'worker_id' => $worker2->id,
    ]);

    // 認証
    $this->actingAs($company);

    // 削除実行 - リダイレクトしないことを確認
    Volt::test('jobs.show', ['jobPost' => $jobPost])
        ->call('delete')
        ->assertNoRedirect();

    // 募集が削除されていないことを確認
    expect(JobPost::find($jobPost->id))->not->toBeNull();
});
