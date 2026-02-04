<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChatRoom;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatRoom>
 */
class ChatRoomFactory extends Factory
{
    /**
     * モデルの名前
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = ChatRoom::class;

    /**
     * モデルのデフォルト状態を定義
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => JobApplication::factory(),
        ];
    }
}
