<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションを実行
     */
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('ユーザーID');
            $table->string('handle_name', 50)->comment('ハンドルネーム');
            $table->string('icon')->nullable()->comment('アイコン画像パス');
            $table->enum('gender', ['male', 'female', 'other'])->comment('性別');
            $table->date('birthdate')->comment('生年月日');
            $table->text('message')->nullable()->comment('ひとことメッセージ（200文字以内）');
            $table->foreignId('birth_location_id')
                ->constrained('locations')
                ->comment('出身地ID（市区町村）');
            $table->foreignId('current_location_1_id')
                ->constrained('locations')
                ->comment('現在のお住まい1ID（市区町村）');
            $table->foreignId('current_location_2_id')
                ->nullable()
                ->constrained('locations')
                ->comment('現在のお住まい2ID（市区町村）');
            $table->timestamps();

            // ユニーク制約
            $table->unique('user_id');
            $table->unique('handle_name');

            // インデックス
            $table->index('birth_location_id');
            $table->index('current_location_1_id');
            $table->index('current_location_2_id');
        });
    }

    /**
     * マイグレーションをロールバック
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};
