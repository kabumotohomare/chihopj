<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * メッセージ送信リクエスト
 */
class StoreMessageRequest extends FormRequest
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
            'message' => ['required', 'string', 'max:1000'],
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
            'message.required' => 'メッセージを入力してください。',
            'message.max' => 'メッセージは1000文字以内で入力してください。',
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
            'message' => 'メッセージ',
        ];
    }
}
