<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Code;
use Illuminate\Database\Seeder;

/**
 * 募集形態・希望・できますのマスタデータシーダー
 */
class CodeSeeder extends Seeder
{
    /**
     * データベースシード実行
     */
    public function run(): void
    {
        $codes = [
            // コード種類定義（type=0）
            ['type' => 0, 'type_id' => 1, 'name' => '雇用形態', 'description' => null, 'sort_order' => 1],
            ['type' => 0, 'type_id' => 2, 'name' => '希望', 'description' => null, 'sort_order' => 2],
            ['type' => 0, 'type_id' => 3, 'name' => 'できます', 'description' => null, 'sort_order' => 3],

            // 募集形態（type=1）
            ['type' => 1, 'type_id' => 1, 'name' => 'プロボノ(無償)', 'description' => null, 'sort_order' => 1],
            ['type' => 1, 'type_id' => 2, 'name' => '副業(有償)', 'description' => null, 'sort_order' => 2],
            ['type' => 1, 'type_id' => 3, 'name' => '採用(有償/雇用)', 'description' => null, 'sort_order' => 3],
            ['type' => 1, 'type_id' => 4, 'name' => '地域おこし協力隊', 'description' => null, 'sort_order' => 4],

            // 希望（type=2）
            ['type' => 2, 'type_id' => 1, 'name' => '若い人大歓迎', 'description' => null, 'sort_order' => 1],
            ['type' => 2, 'type_id' => 2, 'name' => '初めての人大歓迎', 'description' => null, 'sort_order' => 2],
            ['type' => 2, 'type_id' => 3, 'name' => '車を運転できる人', 'description' => null, 'sort_order' => 3],
            ['type' => 2, 'type_id' => 4, 'name' => 'パソコンが得意な人', 'description' => null, 'sort_order' => 4],
            ['type' => 2, 'type_id' => 5, 'name' => '農業に興味がある人', 'description' => null, 'sort_order' => 5],
            ['type' => 2, 'type_id' => 6, 'name' => 'お寺に興味がある人', 'description' => null, 'sort_order' => 6],
            ['type' => 2, 'type_id' => 7, 'name' => '音楽に興味がある人', 'description' => null, 'sort_order' => 7],
            ['type' => 2, 'type_id' => 8, 'name' => 'お祭りに興味がある人', 'description' => null, 'sort_order' => 8],

            // できます（type=3）
            ['type' => 3, 'type_id' => 1, 'name' => '車で迎えに行きます', 'description' => null, 'sort_order' => 1],
            ['type' => 3, 'type_id' => 2, 'name' => 'お食事をご馳走します', 'description' => null, 'sort_order' => 2],
            ['type' => 3, 'type_id' => 3, 'name' => 'お土産をあげます', 'description' => null, 'sort_order' => 3],
            ['type' => 3, 'type_id' => 4, 'name' => '体験ができます', 'description' => null, 'sort_order' => 4],
        ];

        foreach ($codes as $code) {
            Code::query()->create($code);
        }
    }
}
