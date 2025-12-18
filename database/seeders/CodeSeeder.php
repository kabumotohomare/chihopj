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
            ['type' => 2, 'type_id' => 1, 'name' => '提案してほしい', 'description' => null, 'sort_order' => 1],
            ['type' => 2, 'type_id' => 2, 'name' => 'ホームページの制作', 'description' => null, 'sort_order' => 2],
            ['type' => 2, 'type_id' => 3, 'name' => '写真・動画の撮影', 'description' => null, 'sort_order' => 3],
            ['type' => 2, 'type_id' => 4, 'name' => '補助金・助成金の申請', 'description' => null, 'sort_order' => 4],
            ['type' => 2, 'type_id' => 5, 'name' => '従業員の育成・定着', 'description' => null, 'sort_order' => 5],
            ['type' => 2, 'type_id' => 6, 'name' => '労務管理の方法', 'description' => null, 'sort_order' => 6],
            ['type' => 2, 'type_id' => 7, 'name' => 'リノベについて教えて', 'description' => null, 'sort_order' => 7],
            ['type' => 2, 'type_id' => 8, 'name' => 'ITについて教えて', 'description' => null, 'sort_order' => 8],
            ['type' => 2, 'type_id' => 9, 'name' => 'LINEについて教えて', 'description' => null, 'sort_order' => 9],
            ['type' => 2, 'type_id' => 10, 'name' => '通販について教えて', 'description' => null, 'sort_order' => 10],
            ['type' => 2, 'type_id' => 11, 'name' => 'Google MAPについて教えて', 'description' => null, 'sort_order' => 11],
            ['type' => 2, 'type_id' => 12, 'name' => 'インスタグラムについて教えて', 'description' => null, 'sort_order' => 12],
            ['type' => 2, 'type_id' => 13, 'name' => '食品表示について教えて', 'description' => null, 'sort_order' => 13],
            ['type' => 2, 'type_id' => 14, 'name' => 'バイヤーと出会いたい', 'description' => null, 'sort_order' => 14],
            ['type' => 2, 'type_id' => 15, 'name' => '話を聞きに来てほしい', 'description' => null, 'sort_order' => 15],

            // できます（type=3）
            ['type' => 3, 'type_id' => 1, 'name' => '駅まで車出します', 'description' => null, 'sort_order' => 1],
            ['type' => 3, 'type_id' => 2, 'name' => 'お食事しましょう', 'description' => null, 'sort_order' => 2],
            ['type' => 3, 'type_id' => 3, 'name' => 'お土産あげます', 'description' => null, 'sort_order' => 3],
            ['type' => 3, 'type_id' => 4, 'name' => '観光案内します', 'description' => null, 'sort_order' => 4],
            ['type' => 3, 'type_id' => 5, 'name' => '空き部屋に滞在OK', 'description' => null, 'sort_order' => 5],
            ['type' => 3, 'type_id' => 6, 'name' => 'オンラインで話せます', 'description' => null, 'sort_order' => 6],
        ];

        foreach ($codes as $code) {
            Code::query()->create($code);
        }
    }
}
