<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションを実行
     * Workerプロフィールの簡略化：不要な項目を削除し、ひとことメッセージを追加
     */
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            // プロフィール項目を削除
            $table->dropColumn([
                'experiences',
                'want_to_do',
                'good_contribution',
            ]);

            // 移住に関心のある地域を削除（外部キー制約も削除）
            $table->dropForeign(['favorite_location_1_id']);
            $table->dropForeign(['favorite_location_2_id']);
            $table->dropForeign(['favorite_location_3_id']);
            $table->dropIndex(['favorite_location_1_id']);
            $table->dropIndex(['favorite_location_2_id']);
            $table->dropIndex(['favorite_location_3_id']);
            $table->dropColumn([
                'favorite_location_1_id',
                'favorite_location_2_id',
                'favorite_location_3_id',
            ]);

            // 興味のあるお手伝いを削除
            $table->dropColumn('available_action');

            // ひとことメッセージを追加
            $table->text('message')->nullable()->after('birthdate')->comment('ひとことメッセージ（200文字以内）');
        });
    }

    /**
     * マイグレーションをロールバック
     */
    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            // ひとことメッセージを削除
            $table->dropColumn('message');

            // 興味のあるお手伝いを復元
            $table->json('available_action')
                ->nullable()
                ->after('current_location_2_id')
                ->comment('興味のあるお手伝い（JSON配列: mowing, snowplow, diy, localcleaning, volunteer）');

            // 移住に関心のある地域を復元
            $table->foreignId('favorite_location_1_id')
                ->nullable()
                ->after('current_location_2_id')
                ->constrained('locations')
                ->comment('移住に関心のある地域1ID（市区町村）');
            $table->foreignId('favorite_location_2_id')
                ->nullable()
                ->after('favorite_location_1_id')
                ->constrained('locations')
                ->comment('移住に関心のある地域2ID（市区町村）');
            $table->foreignId('favorite_location_3_id')
                ->nullable()
                ->after('favorite_location_2_id')
                ->constrained('locations')
                ->comment('移住に関心のある地域3ID（市区町村）');

            // インデックスを復元
            $table->index('favorite_location_1_id');
            $table->index('favorite_location_2_id');
            $table->index('favorite_location_3_id');

            // プロフィール項目を復元
            $table->text('experiences')->nullable()->after('birthdate')->comment('これまでの経験（200文字以内）');
            $table->text('want_to_do')->nullable()->after('experiences')->comment('これからやりたいこと（200文字以内）');
            $table->text('good_contribution')->nullable()->after('want_to_do')->comment('得意なことや貢献できること（200文字以内）');
        });
    }
};
