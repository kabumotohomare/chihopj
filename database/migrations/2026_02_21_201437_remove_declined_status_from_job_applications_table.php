<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // declined_at カラムを削除
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('declined_at');
        });

        // status ENUMから 'declined' を削除
        DB::statement("ALTER TABLE job_applications MODIFY COLUMN status ENUM('applied', 'accepted', 'rejected') NOT NULL DEFAULT 'applied'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // status ENUMに 'declined' を追加
        DB::statement("ALTER TABLE job_applications MODIFY COLUMN status ENUM('applied', 'accepted', 'rejected', 'declined') NOT NULL DEFAULT 'applied'");

        // declined_at カラムを追加
        Schema::table('job_applications', function (Blueprint $table) {
            $table->datetime('declined_at')->nullable()->after('judged_at');
        });
    }
};
