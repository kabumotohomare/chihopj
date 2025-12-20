<?php

declare(strict_types=1);

use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{layout, state, mount, title};

layout('components.layouts.app.header');
title('応募詳細');

// 状態
state(['application']);

// 初期化
mount(function (JobApplication $jobApplication) {
    // ワーカー本人または募集企業であれば閲覧可能（認可チェック）
    $this->authorize('view', $jobApplication);

    // リレーションを先読み込み
    $this->application = $jobApplication->load([
        'jobPost.company.companyProfile.location',
        'jobPost.jobType',
        'worker.workerProfile.birthLocation',
        'worker.workerProfile.currentLocation1',
        'worker.workerProfile.currentLocation2',
        'worker.workerProfile.favoriteLocation1',
        'worker.workerProfile.favoriteLocation2',
        'worker.workerProfile.favoriteLocation3',
    ]);
});

// 辞退処理
$decline = function () {
    // 認可チェック
    $this->authorize('decline', $this->application);

    // ステータスを更新
    $this->application->update([
        'status' => 'declined',
        'declined_at' => now(),
    ]);

    session()->flash('message', '応募を辞退しました。');

    return $this->redirect(route('applications.index'), navigate: true);
};

// 承認処理
$accept = function () {
    // 認可チェック
    $this->authorize('accept', $this->application);

    // ステータスを更新
    $this->application->update([
        'status' => 'accepted',
        'judged_at' => now(),
    ]);

    session()->flash('message', '応募を承認しました。');

    return $this->redirect(route('applications.received'), navigate: true);
};

// 不承認処理
$reject = function () {
    // 認可チェック
    $this->authorize('reject', $this->application);

    // ステータスを更新
    $this->application->update([
        'status' => 'rejected',
        'judged_at' => now(),
    ]);

    session()->flash('message', '応募を不承認にしました。');

    return $this->redirect(route('applications.received'), navigate: true);
};

// ステータスラベル取得
$getStatusLabel = function (string $status): string {
    return match ($status) {
        'applied' => '応募中',
        'accepted' => '承認済み',
        'rejected' => '不承認',
        'declined' => '辞退済み',
        default => '不明',
    };
};

// ステータスバッジのカラークラス取得
$getStatusBadgeClass = function (string $status): string {
    return match ($status) {
        'applied' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'accepted' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'declined' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    };
};

// 「いつまでに」ラベル取得
$getHowsoonLabel = function (string $howsoon): string {
    return match ($howsoon) {
        'someday' => 'いつか',
        'asap' => 'いますぐにでも',
        default => '不明',
    };
};

?>

<div class="mx-auto max-w-4xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
    {{-- 戻るボタン --}}
    <div>
        @if (auth()->user()->isWorker())
            <flux:button variant="ghost" href="{{ route('applications.index') }}" wire:navigate icon="arrow-left">
                応募一覧に戻る
            </flux:button>
        @else
            <flux:button variant="ghost" href="{{ route('applications.received') }}" wire:navigate icon="arrow-left">
                応募一覧に戻る
            </flux:button>
        @endif
    </div>

    {{-- フラッシュメッセージ --}}
    @if (session()->has('message'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-900 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-900 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- ヘッダー --}}
    <div>
        <flux:heading size="xl" class="mb-2">応募詳細</flux:heading>
        <flux:text>応募情報の詳細を確認できます</flux:text>
    </div>

    @php
        $job = $application->jobPost;
        $company = $job->company;
        $companyProfile = $company->companyProfile;
        $location = $companyProfile?->location;
        $worker = $application->worker;
        $workerProfile = $worker->workerProfile;
    @endphp

    {{-- 応募情報カード --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading size="lg" class="mb-4">応募情報</flux:heading>

        <div class="space-y-4">
            {{-- ステータス --}}
            <div>
                <flux:text class="mb-1 font-semibold">ステータス</flux:text>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $this->getStatusBadgeClass($application->status) }}">
                    {{ $this->getStatusLabel($application->status) }}
                </span>
            </div>

            {{-- 応募日 --}}
            <div>
                <flux:text class="mb-1 font-semibold">応募日</flux:text>
                <flux:text>{{ $application->applied_at->format('Y年n月j日 H:i') }}</flux:text>
            </div>

            {{-- 判定日（承認/不承認の場合のみ） --}}
            @if (in_array($application->status, ['accepted', 'rejected']) && $application->judged_at)
                <div>
                    <flux:text class="mb-1 font-semibold">判定日</flux:text>
                    <flux:text>{{ $application->judged_at->format('Y年n月j日 H:i') }}</flux:text>
                </div>
            @endif

            {{-- 辞退日（辞退済みの場合のみ） --}}
            @if ($application->status === 'declined' && $application->declined_at)
                <div>
                    <flux:text class="mb-1 font-semibold">辞退日</flux:text>
                    <flux:text>{{ $application->declined_at->format('Y年n月j日 H:i') }}</flux:text>
                </div>
            @endif

            {{-- メッセージ --}}
            @if ($application->motive)
                <div>
                    <flux:text class="mb-1 font-semibold">応募メッセージ</flux:text>
                    <flux:text class="whitespace-pre-wrap">{{ $application->motive }}</flux:text>
                </div>
            @endif
        </div>

        {{-- アクションボタン --}}
        @if ($application->status === 'applied')
            <div class="mt-6 flex flex-wrap gap-2">
                {{-- ワーカー向け: 辞退ボタン --}}
                @if (auth()->user()->isWorker())
                    <flux:button variant="danger" wire:click="$dispatch('open-modal', 'decline-modal')">
                        辞退する
                    </flux:button>
                @endif

                {{-- 企業向け: 承認・不承認ボタン --}}
                @if (auth()->user()->isCompany())
                    <flux:button variant="primary" wire:click="$dispatch('open-modal', 'accept-modal')">
                        承認する
                    </flux:button>
                    <flux:button variant="danger" wire:click="$dispatch('open-modal', 'reject-modal')">
                        不承認にする
                    </flux:button>
                @endif
            </div>
        @endif
    </div>

    {{-- ワーカー情報カード（企業向けのみ表示） --}}
    @if (auth()->user()->isCompany() && $workerProfile)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">ワーカー情報</flux:heading>

            <div class="space-y-6">
                {{-- アイコンとハンドルネーム --}}
                <div class="flex items-center gap-4">
                    @if ($workerProfile->icon)
                        <img
                            src="{{ Storage::url($workerProfile->icon) }}"
                            alt="{{ $workerProfile->handle_name }}"
                            class="h-16 w-16 rounded-full object-cover"
                        />
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-purple-500 text-xl font-bold text-white">
                            {{ mb_substr($workerProfile->handle_name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <flux:text class="font-semibold">ハンドルネーム</flux:text>
                        <flux:heading size="md">{{ $workerProfile->handle_name }}</flux:heading>
                    </div>
                </div>

                {{-- 氏名 --}}
                <div>
                    <flux:text class="mb-1 font-semibold">氏名</flux:text>
                    <flux:text>{{ $worker->name }}</flux:text>
                </div>

                {{-- 性別 --}}
                <div>
                    <flux:text class="mb-1 font-semibold">性別</flux:text>
                    <flux:text>{{ $workerProfile->gender_label }}</flux:text>
                </div>

                {{-- 生年月日と年齢 --}}
                <div>
                    <flux:text class="mb-1 font-semibold">生年月日</flux:text>
                    <flux:text>
                        {{ $workerProfile->birthdate->format('Y年n月j日') }}
                        （{{ $workerProfile->age }}歳）
                    </flux:text>
                </div>

                {{-- これまでの経験 --}}
                @if ($workerProfile->experiences)
                    <div>
                        <flux:text class="mb-1 font-semibold">これまでの経験</flux:text>
                        <flux:text class="whitespace-pre-wrap">{{ $workerProfile->experiences }}</flux:text>
                    </div>
                @endif

                {{-- これからやりたいこと --}}
                @if ($workerProfile->want_to_do)
                    <div>
                        <flux:text class="mb-1 font-semibold">これからやりたいこと</flux:text>
                        <flux:text class="whitespace-pre-wrap">{{ $workerProfile->want_to_do }}</flux:text>
                    </div>
                @endif

                {{-- 得意なことや貢献できること --}}
                @if ($workerProfile->good_contribution)
                    <div>
                        <flux:text class="mb-1 font-semibold">得意なことや貢献できること</flux:text>
                        <flux:text class="whitespace-pre-wrap">{{ $workerProfile->good_contribution }}</flux:text>
                    </div>
                @endif

                {{-- 出身地 --}}
                @if ($workerProfile->birthLocation)
                    <div>
                        <flux:text class="mb-1 font-semibold">出身地</flux:text>
                        <flux:text>
                            {{ $workerProfile->birthLocation->prefecture }}
                            {{ $workerProfile->birthLocation->city }}
                        </flux:text>
                    </div>
                @endif

                {{-- 現在のお住まい1 --}}
                @if ($workerProfile->currentLocation1)
                    <div>
                        <flux:text class="mb-1 font-semibold">現在のお住まい1</flux:text>
                        <flux:text>
                            {{ $workerProfile->currentLocation1->prefecture }}
                            {{ $workerProfile->currentLocation1->city }}
                        </flux:text>
                    </div>
                @endif

                {{-- 現在のお住まい2 --}}
                @if ($workerProfile->currentLocation2)
                    <div>
                        <flux:text class="mb-1 font-semibold">現在のお住まい2</flux:text>
                        <flux:text>
                            {{ $workerProfile->currentLocation2->prefecture }}
                            {{ $workerProfile->currentLocation2->city }}
                        </flux:text>
                    </div>
                @endif

                {{-- 移住に関心のある地域1 --}}
                @if ($workerProfile->favoriteLocation1)
                    <div>
                        <flux:text class="mb-1 font-semibold">移住に関心のある地域1</flux:text>
                        <flux:text>
                            {{ $workerProfile->favoriteLocation1->prefecture }}
                            {{ $workerProfile->favoriteLocation1->city }}
                        </flux:text>
                    </div>
                @endif

                {{-- 移住に関心のある地域2 --}}
                @if ($workerProfile->favoriteLocation2)
                    <div>
                        <flux:text class="mb-1 font-semibold">移住に関心のある地域2</flux:text>
                        <flux:text>
                            {{ $workerProfile->favoriteLocation2->prefecture }}
                            {{ $workerProfile->favoriteLocation2->city }}
                        </flux:text>
                    </div>
                @endif

                {{-- 移住に関心のある地域3 --}}
                @if ($workerProfile->favoriteLocation3)
                    <div>
                        <flux:text class="mb-1 font-semibold">移住に関心のある地域3</flux:text>
                        <flux:text>
                            {{ $workerProfile->favoriteLocation3->prefecture }}
                            {{ $workerProfile->favoriteLocation3->city }}
                        </flux:text>
                    </div>
                @endif

                {{-- 興味のあるお手伝い --}}
                @if ($workerProfile->available_action_labels)
                    <div>
                        <flux:text class="mb-2 font-semibold">興味のあるお手伝い</flux:text>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($workerProfile->available_action_labels as $label)
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    {{ $label }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- 募集情報カード --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading size="lg" class="mb-4">募集情報</flux:heading>

        {{-- アイキャッチ画像 --}}
        @if ($job->eyecatch)
            @if (str_starts_with($job->eyecatch, '/images/presets/'))
                <img
                    src="{{ $job->eyecatch }}"
                    alt="{{ $job->job_title }}"
                    class="mb-6 aspect-video w-full rounded-lg object-cover"
                />
            @else
                <img
                    src="{{ Storage::url($job->eyecatch) }}"
                    alt="{{ $job->job_title }}"
                    class="mb-6 aspect-video w-full rounded-lg object-cover"
                />
            @endif
        @else
            <div class="mb-6 aspect-video w-full rounded-lg bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900 dark:to-purple-900"></div>
        @endif

        <div class="space-y-6">
            {{-- タグエリア --}}
            <div class="flex flex-wrap gap-2">
                {{-- 募集形態タグ --}}
                @if ($job->jobType)
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $job->jobType->name }}
                    </span>
                @endif

                {{-- 希望タグ --}}
                @foreach ($job->getWantYouCodes() as $wantYou)
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        #{{ $wantYou->name }}
                    </span>
                @endforeach

                {{-- いつまでに --}}
                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                    {{ $this->getHowsoonLabel($job->howsoon) }}
                </span>
            </div>

            {{-- やりたいこと --}}
            <div>
                <flux:text class="mb-1 font-semibold">やりたいこと</flux:text>
                <flux:heading size="md">{{ $job->job_title }}</flux:heading>
            </div>

            {{-- 事業内容・困っていること --}}
            <div>
                <flux:text class="mb-1 font-semibold">事業内容・困っていること</flux:text>
                <flux:text class="whitespace-pre-wrap">{{ $job->job_detail }}</flux:text>
            </div>

            {{-- できますタグ --}}
            @if ($job->getCanDoCodes()->isNotEmpty())
                <div>
                    <flux:text class="mb-2 font-semibold">御礼に</flux:text>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($job->getCanDoCodes() as $canDo)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                {{ $canDo->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 企業名と所在地 --}}
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <flux:icon.building-office class="h-4 w-4" />
                <span>{{ $company->name }}</span>
                @if ($location)
                    <span>・</span>
                    <span>{{ $location->prefecture }} {{ $location->city }}</span>
                @endif
            </div>

            {{-- 投稿日 --}}
            <div class="text-sm text-gray-500 dark:text-gray-400">
                投稿日: {{ $job->posted_at->format('Y年n月j日') }}
            </div>
        </div>

        {{-- 募集詳細へのリンク --}}
        <div class="mt-6">
            <flux:button href="{{ route('jobs.show', $job) }}" wire:navigate>
                募集詳細を見る
            </flux:button>
        </div>
    </div>

    {{-- 辞退確認モーダル --}}
    <flux:modal name="decline-modal" class="space-y-6">
        <div>
            <flux:heading size="lg">応募を辞退しますか？</flux:heading>
            <flux:text class="mt-2">
                この操作は取り消せません。本当に辞退してもよろしいですか？
            </flux:text>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" wire:click="$dispatch('close-modal', 'decline-modal')">
                キャンセル
            </flux:button>
            <flux:button variant="danger" wire:click="decline">
                辞退する
            </flux:button>
        </div>
    </flux:modal>

    {{-- 承認確認モーダル --}}
    <flux:modal name="accept-modal" class="space-y-6">
        <div>
            <flux:heading size="lg">応募を承認しますか？</flux:heading>
            <flux:text class="mt-2">
                この応募を承認してもよろしいですか？承認後、ワーカーに通知されます。
            </flux:text>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" wire:click="$dispatch('close-modal', 'accept-modal')">
                キャンセル
            </flux:button>
            <flux:button variant="primary" wire:click="accept">
                承認する
            </flux:button>
        </div>
    </flux:modal>

    {{-- 不承認確認モーダル --}}
    <flux:modal name="reject-modal" class="space-y-6">
        <div>
            <flux:heading size="lg">応募を不承認にしますか？</flux:heading>
            <flux:text class="mt-2">
                この操作は取り消せません。本当に不承認にしてもよろしいですか？
            </flux:text>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" wire:click="$dispatch('close-modal', 'reject-modal')">
                キャンセル
            </flux:button>
            <flux:button variant="danger" wire:click="reject">
                不承認にする
            </flux:button>
        </div>
    </flux:modal>
</div>
