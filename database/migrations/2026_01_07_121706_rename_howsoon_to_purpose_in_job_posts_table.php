<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->renameColumn('howsoon', 'purpose');
        });

        // 既存データの値を新しい値にマッピング（オプション）
        // 既存データを削除する場合は不要
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->renameColumn('purpose', 'howsoon');
        });
    }
};
