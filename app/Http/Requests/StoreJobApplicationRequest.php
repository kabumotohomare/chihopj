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
            'reasons.*' => ['string', 'in:where_to_meet,what_time_ends,will_pick_up,what_to_bring,late_join_ok,children_ok'],
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
            'reasons.array' => '気になる点の形式が正しくありません。',
            'reasons.*.in' => '選択された気になる点が無効です。',
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
            'reasons' => '気になる点',
            'motive' => 'メッセージ',
        ];
    }
}
