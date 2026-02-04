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
        // 事業承継・M&Aの相談 → 削除
        Code::query()
            ->where('type', 2)
            ->where('name', '事業承継・M&Aの相談')
            ->delete();

        // 収穫作業を手伝ってほしい → 削除
        Code::query()
            ->where('type', 2)
            ->where('name', '収穫作業を手伝ってほしい')
            ->delete();

        // 清掃を手伝ってほしい → 削除
        Code::query()
            ->where('type', 2)
            ->where('name', '清掃を手伝ってほしい')
            ->delete();

        // 草刈りを手伝ってほしい → 草刈り機を使える人
        Code::query()
            ->where('type', 2)
            ->where('name', '草刈りを手伝ってほしい')
            ->update(['name' => '草刈り機を使える人']);

        // 雪かきを手伝ってほしい → 雪のなかでも平気な人
        Code::query()
            ->where('type', 2)
            ->where('name', '雪かきを手伝ってほしい')
            ->update(['name' => '雪のなかでも平気な人']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 変更を元に戻す
        Code::query()
            ->where('type', 2)
            ->where('name', '草刈り機を使える人')
            ->update(['name' => '草刈りを手伝ってほしい']);

        Code::query()
            ->where('type', 2)
            ->where('name', '雪のなかでも平気な人')
            ->update(['name' => '雪かきを手伝ってほしい']);

        // 削除されたレコードは復元しない（元のtype_idが不明なため）
        // 必要に応じて手動で復元してください
    }
};
