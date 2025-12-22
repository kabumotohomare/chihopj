<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ChatRoom;
use App\Models\JobApplication;
use Illuminate\Database\Seeder;

/**
 * チャットルームシーダー
 */
class ChatRoomSeeder extends Seeder
{
    /**
     * データベースシード実行
     */
    public function run(): void
    {
        // すべての応募を取得
        $applications = JobApplication::query()->get();

        foreach ($applications as $application) {
            // 既にchat_roomが存在するかチェック
            $existingChatRoom = ChatRoom::query()
                ->where('application_id', $application->id)
                ->first();

            // 存在しない場合のみ作成
            if ($existingChatRoom === null) {
                ChatRoom::query()->create([
                    'application_id' => $application->id,
                ]);
            }
        }
    }
}
