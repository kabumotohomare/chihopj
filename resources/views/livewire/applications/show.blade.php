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
    // ひらいず民本人または募集ホストであれば閲覧可能（認可チェック）
    $this->authorize('view', $jobApplication);

    // リレーションを先読み込み
    $this->application = $jobApplication->load([
        'jobPost.company.companyProfile',
        'jobPost.jobType',
        'worker.workerProfile.birthLocation',
        'worker.workerProfile.currentLocation1',
        'worker.workerProfile.currentLocation2',
        'chatRoom',
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

// ぜひ来てね処理
$accept = function () {
    // 認可チェック
    $this->authorize('accept', $this->application);

    // ステータスを更新
    $this->application->update([
        'status' => 'accepted',
        'judged_at' => now(),
    ]);

    session()->flash('message', '「ぜひ来てね」を送信しました。');

    return $this->redirect(route('applications.received'), navigate: true);
};

// 今回ごめんね処理
$reject = function () {
    // 認可チェック
    $this->authorize('reject', $this->application);

    // ステータスを更新
    $this->application->update([
        'status' => 'rejected',
        'judged_at' => now(),
    ]);

    session()->flash('message', '「今回ごめんね」を送信しました。');

    return $this->redirect(route('applications.received'), navigate: true);
};

// ステータスラベル取得
$getStatusLabel = function (string $status): string {
    return match ($status) {
        'applied' => '応募中',
        'accepted' => 'ぜひ来てね',
        'rejected' => '今回ごめんね',
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

// 募集目的ラベル取得
$getPurposeLabel = function (string $purpose): string {
    return match ($purpose) {
        'want_to_do' => 'いつでも募集',
        'need_help' => '決まった日に募集',
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
        
        // 気になる点のラベルマッピング
        $reasonLabels = [
            'where_to_meet' => '集合はどこ？',
            'what_time_ends' => '何時に終わる？',
            'will_pick_up' => '迎えに来てくれる？',
            'what_to_bring' => '持ち物は何が必要？',
            'late_join_ok' => '遅れて参加でも良い？',
            'children_ok' => '子どもと一緒でも大丈夫？',
        ];
    @endphp

    {{-- ひらいず民情報カード（ホスト向けのみ表示） --}}
    @if (auth()->user()->isCompany() && $workerProfile)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">ひらいず民情報</flux:heading>

            <div class="space-y-6">
                {{-- アイコンとニックネーム --}}
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
                        <flux:text class="font-semibold">ニックネーム</flux:text>
                        <flux:heading size="md">{{ $workerProfile->handle_name }}</flux:heading>
                    </div>
                </div>

                {{-- 性別 --}}
                <div>
                    <flux:text class="mb-1 font-semibold">性別</flux:text>
                    <flux:text>{{ $workerProfile->gender_label }}</flux:text>
                </div>

                {{-- 年齢 --}}
                <div>
                    <flux:text class="mb-1 font-semibold">年齢</flux:text>
                    <flux:text>{{ $workerProfile->age }}歳</flux:text>
                </div>

                {{-- ひとことメッセージ --}}
                @if ($workerProfile->message)
                    <div>
                        <flux:text class="mb-1 font-semibold">ひとことメッセージ</flux:text>
                        <flux:text class="whitespace-pre-wrap">{{ $workerProfile->message }}</flux:text>
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

            </div>
        </div>
    @endif

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

            {{-- 判定日（ぜひ来てね/今回ごめんねの場合のみ） --}}
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

            {{-- 募集で気になる点は？ --}}
            @if (!empty($application->reasons) && is_array($application->reasons))
                <div>
                    <flux:text class="mb-2 font-semibold">募集で気になる点は？</flux:text>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($application->reasons as $reason)
                            @if (isset($reasonLabels[$reason]))
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $reasonLabels[$reason] }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 応募メッセージ --}}
            @if ($application->motive)
                <div>
                    <flux:text class="mb-1 font-semibold">応募メッセージ</flux:text>
                    <flux:text class="whitespace-pre-wrap">{{ $application->motive }}</flux:text>
                </div>
            @endif
        </div>

        {{-- アクションボタン --}}
        <div class="mt-6 flex flex-wrap gap-2">
            {{-- チャットボタン（チャットルームが存在する場合） --}}
            @if ($application->chatRoom)
                <flux:button variant="primary" href="{{ route('chats.show', $application->chatRoom) }}" wire:navigate icon="chat-bubble-left-right">
                    チャットで返信
                </flux:button>
            @endif

            @if ($application->status === 'applied')
                {{-- ひらいず民向け: 辞退ボタン --}}
                @if (auth()->user()->isWorker())
                    <flux:modal.trigger name="decline-modal">
                        <flux:button variant="danger">
                            辞退する
                        </flux:button>
                    </flux:modal.trigger>
                @endif

                {{-- ホスト向け: ぜひ来てね・今回ごめんねボタン --}}
                @if (auth()->user()->isCompany())
                    <flux:modal.trigger name="accept-modal">
                        <flux:button variant="primary">
                            ぜひ来てね
                        </flux:button>
                    </flux:modal.trigger>
                    <flux:modal.trigger name="reject-modal">
                        <flux:button variant="danger">
                            今回ごめんね
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            @endif
        </div>
    </div>

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
                    {{ $this->getPurposeLabel($job->purpose) }}
                </span>
            </div>

            {{-- やること --}}
            <div>
                <flux:text class="mb-1 font-semibold">やること</flux:text>
                <flux:heading size="md">{{ $job->job_title }}</flux:heading>
            </div>

            {{-- 具体的にはこんなことを手伝ってほしい --}}
            <div>
                <flux:text class="mb-1 font-semibold">具体的にはこんなことを手伝ってほしい</flux:text>
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

            {{-- ホスト名 --}}
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <flux:icon.building-office class="h-4 w-4" />
                <span>{{ $company->name }}</span>
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
            <flux:modal.close>
                <flux:button variant="ghost">
                    キャンセル
                </flux:button>
            </flux:modal.close>
            <flux:modal.close>
                <flux:button variant="danger" wire:click="decline">
                    辞退する
                </flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

    {{-- ぜひ来てね確認モーダル --}}
    <flux:modal name="accept-modal" class="space-y-6">
        <div>
            <flux:heading size="lg">「ぜひ来てね」を送信しますか？</flux:heading>
            <flux:text class="mt-2">
                この応募に「ぜひ来てね」を送信してもよろしいですか？送信後、ひらいず民に通知されます。
            </flux:text>
        </div>

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">
                    キャンセル
                </flux:button>
            </flux:modal.close>
            <flux:modal.close>
                <flux:button variant="primary" wire:click="accept">
                    ぜひ来てね
                </flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

    {{-- 今回ごめんね確認モーダル --}}
    <flux:modal name="reject-modal" class="space-y-6">
        <div>
            <flux:heading size="lg">「今回ごめんね」を送信しますか？</flux:heading>
            <flux:text class="mt-2">
                この操作は取り消せません。本当に「今回ごめんね」を送信してもよろしいですか？
            </flux:text>
        </div>

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">
                    キャンセル
                </flux:button>
            </flux:modal.close>
            <flux:modal.close>
                <flux:button variant="danger" wire:click="reject">
                    今回ごめんね
                </flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</div>
