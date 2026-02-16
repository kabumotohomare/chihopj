<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーション実行
     *
     * job_postsテーブルにlocationカラムを追加
     */
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('location', 200)->nullable()->after('job_detail')->comment('どこで（活動場所）');
        });
    }

    /**
     * マイグレーションロールバック
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
