<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーション実行
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->json('reasons')->nullable()->after('motive')->comment('応募理由（複数選択可）');
        });
    }

    /**
     * マイグレーションのロールバック
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('reasons');
        });
    }
};
