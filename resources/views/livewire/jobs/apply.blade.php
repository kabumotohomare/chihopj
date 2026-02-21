<?php

declare(strict_types=1);

use App\Models\ChatRoom;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.app.header');
title('応募する');

state(['jobPost', 'reasons' => [], 'motive' => '']);

/**
 * コンポーネント初期化
 */
mount(function (JobPost $jobPost) {
    // プロフィール未登録の場合はひらいず民プロフィール登録画面にリダイレクト
    if (auth()->user()->workerProfile === null) {
        session()->flash('error', '応募する場合はひらいず民プロフィール登録を完了させてください');

        return $this->redirect(route('worker.register'), navigate: true);
    }

    // リレーションを先読み込み
    $this->jobPost = $jobPost->load(['company.companyProfile']);

    // 応募可否チェック（ポリシーで重複応募もチェック）
    $this->authorize('apply', $jobPost);
});

/**
 * 応募処理
 */
$submit = function () {
    // バリデーション
    $validated = $this->validate(
        [
            'reasons' => 'nullable|array',
            'reasons.*' => 'string|in:where_to_meet,what_time_ends,will_pick_up,what_to_bring,late_join_ok,children_ok',
            'motive' => 'nullable|string|max:1000',
        ],
        [
            'reasons.array' => '気になる点の形式が正しくありません。',
            'reasons.*.in' => '選択された気になる点が無効です。',
            'motive.max' => 'メッセージは1000文字以内で入力してください。',
        ],
    );

    // ポリシーで認可チェック（重複応募チェックを含む）
    $this->authorize('apply', $this->jobPost);

    // トランザクション内で応募データ、チャットルーム、初回メッセージを作成
    DB::transaction(function () use ($validated) {
        // 1. 応募データ（JobApplication）を登録
        $jobApplication = JobApplication::create([
            'job_id' => $this->jobPost->id,
            'worker_id' => auth()->id(),
            'reasons' => !empty($validated['reasons']) ? $validated['reasons'] : null,
            'motive' => $validated['motive'] ?: null,
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        // 2. 登録成功後、ChatRoomを自動作成（application_idを設定）
        $chatRoom = ChatRoom::firstOrCreate(
            ['application_id' => $jobApplication->id],
            [
                'application_id' => $jobApplication->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        // 3. 応募内容をチャットメッセージとして投稿
        // 気になる点と応募メッセージを結合
        $messageContent = '';

        // 気になる点がある場合
        if (!empty($validated['reasons'])) {
            $reasonsLabels = [
                'where_to_meet' => '集合はどこ？',
                'what_time_ends' => '何時に終わる？',
                'will_pick_up' => '迎えに来てくれる？',
                'what_to_bring' => '持ち物は何が必要？',
                'late_join_ok' => '遅れて参加でも良い？',
                'children_ok' => '子どもと一緒でも大丈夫？',
            ];

            $messageContent .= "【募集で気になる点】\n";
            foreach ($validated['reasons'] as $reason) {
                $messageContent .= '・' . $reasonsLabels[$reason] . "\n";
            }
            $messageContent .= "\n";
        }

        // 応募メッセージがある場合
        if (!empty($validated['motive'])) {
            if (!empty($messageContent)) {
                $messageContent .= "【応募メッセージ】\n";
            }
            $messageContent .= $validated['motive'];
        }

        // メッセージがある場合のみチャットに投稿
        if (!empty($messageContent)) {
            Message::create([
                'chat_room_id' => $chatRoom->id,
                'sender_id' => auth()->id(),
                'message' => $messageContent,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    });

    session()->flash('status', '応募が完了しました。');

    // 4. ひらいず民募集詳細画面にリダイレクト
    return $this->redirect(route('jobs.show', $this->jobPost), navigate: true);
};

/**
 * 募集目的のラベル取得
 */
$getPurposeLabel = function (): string {
    return $this->jobPost->getPurposeLabel();
};

?>

<div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-900">
    <div class="container mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <!-- 戻るボタン -->
        <div class="mb-6">
            <flux:button href="{{ route('jobs.show', $jobPost) }}" wire:navigate variant="ghost" icon="arrow-left">
                募集詳細に戻る
            </flux:button>
        </div>

        <!-- ページタイトル -->
        <div class="mb-6">
            <flux:heading size="xl" class="text-gray-900 dark:text-white">
                応募する
            </flux:heading>
        </div>

        <!-- 注意喚起メッセージ -->
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            <strong>まだ応募は完了していません。</strong><br>
            下から、ホストに向けて気になる点の質問やメッセージ(任意)を作成し、「応募する」を押してください。
        </flux:callout>

        <!-- 募集情報サマリー -->
        <div class="mb-8 overflow-hidden rounded-xl bg-white shadow dark:bg-gray-800">
            <!-- アイキャッチ画像 -->
            @if ($jobPost->eyecatch)
                <div class="aspect-video w-full overflow-hidden">
                    @if (str_starts_with($jobPost->eyecatch, '/images/presets/'))
                        <img src="{{ $jobPost->eyecatch }}" alt="{{ $jobPost->job_title }}"
                            class="h-full w-full object-cover">
                    @else
                        <img src="{{ Storage::url($jobPost->eyecatch) }}" alt="{{ $jobPost->job_title }}"
                            class="h-full w-full object-cover">
                    @endif
                </div>
            @endif

            <div class="p-6 sm:p-8">
                <!-- タグエリア -->
                <div class="mb-4 flex flex-wrap gap-2">
                    <!-- 希望タグ -->
                    @foreach ($jobPost->getWantYouCodes() as $code)
                        <flux:badge color="zinc" size="sm" class="rounded-full">
                            #{{ $code->name }}
                        </flux:badge>
                    @endforeach
                </div>

                <!-- 募集見出し -->
                <div class="mb-4 flex items-start gap-3">
                    <flux:badge color="red" size="lg" class="flex-shrink-0 font-bold">
                        {{ $this->getPurposeLabel() }}
                    </flux:badge>
                    <flux:heading size="lg" class="flex-1 text-gray-900 dark:text-white">
                        {{ $jobPost->job_title }}
                    </flux:heading>
                </div>

                <!-- 事業内容 -->
                <div class="mb-4">
                    <flux:text class="whitespace-pre-wrap text-gray-600 dark:text-gray-400">
                        {{ Str::limit($jobPost->job_detail, 90) }}
                    </flux:text>
                </div>

                <!-- どこで -->
                @if ($jobPost->location)
                    <div class="mb-4 flex items-start gap-2">
                        <flux:icon.map-pin variant="micro"
                            class="mt-0.5 flex-shrink-0 text-gray-500 dark:text-gray-400" />
                        <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $jobPost->location }}
                        </flux:text>
                    </div>
                @endif

                <!-- できますタグ -->
                @if ($jobPost->getCanDoCodes()->isNotEmpty())
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($jobPost->getCanDoCodes() as $code)
                                <flux:badge color="green" size="sm" class="rounded-full">
                                    ✓ {{ $code->name }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- ホスト情報 -->
                <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- ホスト名 -->
                        <div class="flex items-center gap-2">
                            <flux:badge color="zinc" size="sm">
                                <span class="flex items-center gap-1">
                                    <flux:icon.building-office-2 variant="micro" />
                                    {{ $jobPost->company->name }}
                                </span>
                            </flux:badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 応募フォーム -->
        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-800">
            <div class="p-6 sm:p-8">
                <form wire:submit.prevent="submit">
                    <!-- この募集で気になる点 -->
                    <div class="mb-8">
                        <flux:heading size="lg" class="mb-4 text-gray-900 dark:text-white">
                            この募集で気になる点は？
                        </flux:heading>

                        <flux:text variant="subtle" class="mb-4">
                            当てはまるものをすべて選択してください（任意）
                        </flux:text>

                        <div class="space-y-3">
                            <label
                                class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                                <input type="checkbox" wire:model="reasons" value="where_to_meet"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-600">
                                <span class="text-gray-700 dark:text-gray-300">集合はどこ？</span>
                            </label>

                            <label
                                class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                                <input type="checkbox" wire:model="reasons" value="what_time_ends"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-600">
                                <span class="text-gray-700 dark:text-gray-300">何時に終わる？</span>
                            </label>

                            <label
                                class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                                <input type="checkbox" wire:model="reasons" value="will_pick_up"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-600">
                                <span class="text-gray-700 dark:text-gray-300">迎えに来てくれる？</span>
                            </label>

                            <label
                                class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                                <input type="checkbox" wire:model="reasons" value="what_to_bring"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-600">
                                <span class="text-gray-700 dark:text-gray-300">持ち物は何が必要？</span>
                            </label>

                            <label
                                class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                                <input type="checkbox" wire:model="reasons" value="late_join_ok"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-600">
                                <span class="text-gray-700 dark:text-gray-300">遅れて参加でも良い？</span>
                            </label>

                            <label
                                class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                                <input type="checkbox" wire:model="reasons" value="children_ok"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-600">
                                <span class="text-gray-700 dark:text-gray-300">子どもと一緒でも大丈夫？</span>
                            </label>
                        </div>

                        <flux:error name="reasons" />
                    </div>

                    <!-- 応募メッセージ -->
                    <div class="mb-8">
                        <flux:field>
                            <flux:label>応募メッセージ（任意）</flux:label>
                            <flux:textarea wire:model="motive" rows="6" placeholder="意気込み、質問や確認したいことなど、自由にどうぞ">
                                {{ $motive }}</flux:textarea>
                            <flux:error name="motive" />
                            <flux:description>
                                1000文字以内で入力してください。
                            </flux:description>
                        </flux:field>
                    </div>

                    <!-- 応募ボタン -->
                    <div class="flex flex-wrap gap-3">
                        <flux:modal.trigger name="confirm-apply">
                            <flux:button variant="primary" icon="paper-airplane" class="flex-1 sm:flex-none">
                                応募する
                            </flux:button>
                        </flux:modal.trigger>

                        <flux:button href="{{ route('jobs.show', $jobPost) }}" wire:navigate variant="ghost"
                            class="flex-1 sm:flex-none">
                            キャンセル
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 応募確認モーダル -->
    <flux:modal name="confirm-apply" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">本当に応募しますか？</flux:heading>
                <flux:subheading class="mt-2">
                    応募すると、あなたのニックネーム、性別、年齢、市区町村までのお住まいの情報が、募集しているホストに送られます（応募の時点では、個人が特定される情報は送られません）。応募を確定して構いませんか？
                </flux:subheading>
            </div>

            <div class="flex gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost" class="flex-1">
                        キャンセル
                    </flux:button>
                </flux:modal.close>

                <flux:button wire:click="submit" variant="primary" class="flex-1" wire:loading.attr="disabled">
                    <span wire:loading.remove>応募する</span>
                    <span wire:loading>処理中...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
