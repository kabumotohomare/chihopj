<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * enum に municipal（役所）ロールを追加
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('company', 'worker', 'admin', 'municipal') NOT NULL COMMENT 'ユーザーロール: company=企業, worker=ワーカー, admin=管理者, municipal=役所'");
    }

    /**
     * municipal ロールを削除（ロールバック）
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('company', 'worker', 'admin') NOT NULL COMMENT 'ユーザーロール: company=企業, worker=ワーカー, admin=管理者'");
    }
};
