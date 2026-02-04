<?php

declare(strict_types=1);

use App\Models\Code;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 希望（type=2）のレコードを新しい内容に更新
        Code::query()
            ->where('type', 2)
            ->where('type_id', 1)
            ->update(['name' => '若い人大歓迎']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 2)
            ->update(['name' => '初めての人大歓迎']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 3)
            ->update(['name' => '車を運転できる人']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 4)
            ->update(['name' => 'パソコンが得意な人']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 5)
            ->update(['name' => '農業に興味がある人']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 6)
            ->update(['name' => 'お寺に興味がある人']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 7)
            ->update(['name' => '音楽に興味がある人']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 8)
            ->update(['name' => 'お祭りに興味がある人']);

        // type_id 9-15 のレコードを削除
        Code::query()
            ->where('type', 2)
            ->whereIn('type_id', [9, 10, 11, 12, 13, 14, 15])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元の値に戻す
        Code::query()
            ->where('type', 2)
            ->where('type_id', 1)
            ->update(['name' => '提案してほしい']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 2)
            ->update(['name' => 'ホームページの制作']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 3)
            ->update(['name' => '写真・動画の撮影']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 4)
            ->update(['name' => '補助金・助成金の申請']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 5)
            ->update(['name' => '従業員の育成・定着']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 6)
            ->update(['name' => '労務管理の方法']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 7)
            ->update(['name' => 'リノベについて教えて']);

        Code::query()
            ->where('type', 2)
            ->where('type_id', 8)
            ->update(['name' => 'ITについて教えて']);

        // 削除されたレコードを復元
        Code::query()->create([
            'type' => 2,
            'type_id' => 9,
            'name' => 'LINEについて教えて',
            'description' => null,
            'sort_order' => 9,
        ]);

        Code::query()->create([
            'type' => 2,
            'type_id' => 10,
            'name' => '通販について教えて',
            'description' => null,
            'sort_order' => 10,
        ]);

        Code::query()->create([
            'type' => 2,
            'type_id' => 11,
            'name' => 'Google MAPについて教えて',
            'description' => null,
            'sort_order' => 11,
        ]);

        Code::query()->create([
            'type' => 2,
            'type_id' => 12,
            'name' => 'インスタグラムについて教えて',
            'description' => null,
            'sort_order' => 12,
        ]);

        Code::query()->create([
            'type' => 2,
            'type_id' => 13,
            'name' => '食品表示について教えて',
            'description' => null,
            'sort_order' => 13,
        ]);

        Code::query()->create([
            'type' => 2,
            'type_id' => 14,
            'name' => 'バイヤーと出会いたい',
            'description' => null,
            'sort_order' => 14,
        ]);

        Code::query()->create([
            'type' => 2,
            'type_id' => 15,
            'name' => '話を聞きに来てほしい',
            'description' => null,
            'sort_order' => 15,
        ]);
    }
};
