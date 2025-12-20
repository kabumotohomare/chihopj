<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 応募作成リクエスト
 */
class StoreJobApplicationRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを実行する権限があるか判定
     */
    public function authorize(): bool
    {
        return true; // ポリシーで認可チェックを行うためtrueを返す
    }

    /**
     * バリデーションルール
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'reasons' => ['nullable', 'array'],
            'reasons.*' => ['string', 'in:near_hometown,lived_before,wanted_to_visit,empathize_with_goal,travel_opportunity,can_use_experience,wanted_to_try,gain_new_experience'],
            'motive' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * バリデーションエラーメッセージ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reasons.array' => '応募理由の形式が正しくありません。',
            'reasons.*.in' => '選択された応募理由が無効です。',
            'motive.max' => 'メッセージは1000文字以内で入力してください。',
        ];
    }

    /**
     * フィールドの日本語名
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'reasons' => '応募理由',
            'motive' => 'メッセージ',
        ];
    }
}
