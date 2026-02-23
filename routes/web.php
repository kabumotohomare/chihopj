<?php

use App\Models\JobPost;
use App\Models\Location;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    // デバッグモード：クエリパラメータで有効化
    if (request()->has('debug_locations')) {
        try {
            $prefectures = \App\Models\Location::whereNull('city')->orderBy('code')->get();
            $cities = \App\Models\Location::whereNotNull('city')->orderBy('code')->take(10)->get();
            
            $data = [
                'status' => 'success',
                'prefectures_count' => $prefectures->count(),
                'prefectures_sample' => $prefectures->take(5)->pluck('prefecture')->toArray(),
                'cities_count' => \App\Models\Location::whereNotNull('city')->count(),
                'cities_sample' => $cities->map(fn($c) => $c->prefecture . ' ' . $c->city)->toArray(),
                'database_connected' => true,
            ];
            
            // 必ずJSONとして出力
            return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'database_connected' => false,
            ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }
    
    // 最新の募集を取得（Eager Loading）
    $latestJobs = JobPost::query()
        ->with(['company.companyProfile.location', 'jobType'])
        ->latest('posted_at')
        ->take(3)
        ->get();

    return view('welcome', [
        'latestJobs' => $latestJobs,
    ]);
})->name('welcome');

Volt::route('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ひらいず民登録（未認証ユーザー向け）
Volt::route('worker/register', 'worker.register')
    ->name('worker.register');

// ひらいず民プロフィール
Volt::route('worker/profile', 'worker.show')
    ->middleware(['auth', 'role:worker'])
    ->name('worker.profile');

// ひらいず民プロフィール編集
Volt::route('worker/edit', 'worker.edit')
    ->middleware(['auth', 'role:worker'])
    ->name('worker.edit');

// ホスト登録（認証済みユーザー向け）
Volt::route('company/register', 'company.register')
    ->middleware(['auth'])
    ->name('company.register');

// ホストプロフィール
Volt::route('company/profile', 'company.show')
    ->middleware(['auth', 'role:company'])
    ->name('company.profile');

// ホストプロフィール編集
Volt::route('company/edit', 'company.edit')
    ->middleware(['auth', 'role:company'])
    ->name('company.edit');

// 募集一覧（誰でも閲覧可能）
Volt::route('jobs', 'jobs.index')
    ->name('jobs.index');

// 自社の募集一覧（ホストユーザーのみ）※動的ルートより前に配置
Volt::route('jobs/my-jobs', 'jobs.my-jobs')
    ->middleware(['auth', 'role:company'])
    ->name('jobs.my-jobs');

// 募集登録（ホストユーザーのみ）※動的ルートより前に配置
Volt::route('jobs/create', 'jobs.create')
    ->middleware(['auth', 'role:company'])
    ->name('jobs.create');

// 募集詳細（誰でも閲覧可能）
Volt::route('jobs/{jobPost}', 'jobs.show')
    ->name('jobs.show');

// 募集編集（ホストユーザーのみ、自社ひらいず民募集のみ）
Volt::route('jobs/{jobPost}/edit', 'jobs.edit')
    ->middleware(['auth', 'role:company'])
    ->name('jobs.edit');

// 応募画面（ひらいず民のみ）
Volt::route('jobs/{jobPost}/apply', 'jobs.apply')
    ->middleware(['auth', 'role:worker'])
    ->name('jobs.apply');

// 応募一覧（ひらいず民のみ）
Volt::route('applications', 'applications.index')
    ->middleware(['auth', 'role:worker'])
    ->name('applications.index');

// 応募一覧（ホスト向け、自社募集への応募）※動的ルートより前に配置
Volt::route('applications/received', 'applications.received')
    ->middleware(['auth', 'role:company'])
    ->name('applications.received');

// 応募詳細（ひらいず民とホストが閲覧可能、認可はポリシーで制御）
Volt::route('applications/{jobApplication}', 'applications.show')
    ->middleware(['auth'])
    ->name('applications.show');

// チャット一覧（認証済みユーザー）
Volt::route('chats', 'chats.index')
    ->middleware(['auth'])
    ->name('chats.index');

// チャット詳細（認証済みユーザー、認可はポリシーで制御）※動的ルートより前に配置
Volt::route('chats/{chatRoom}', 'chats.show')
    ->middleware(['auth'])
    ->name('chats.show');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
});
