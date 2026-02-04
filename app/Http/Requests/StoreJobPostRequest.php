<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 募集投稿のバリデーションリクエスト
 */
class StoreJobPostRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを実行する権限があるかを判定
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを取得
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'eyecatch' => ['nullable', 'image', 'max:2048'],
            'purpose' => ['required', 'in:want_to_do,need_help'],
            'job_title' => ['required', 'string', 'max:50'],
            'job_detail' => ['required', 'string', 'max:200'],
            'want_you_ids' => ['nullable', 'array'],
            'want_you_ids.*' => ['integer', 'exists:codes,id'],
            'can_do_ids' => ['nullable', 'array'],
            'can_do_ids.*' => ['integer', 'exists:codes,id'],
        ];
    }

    /**
     * カスタムバリデーションメッセージを取得
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'eyecatch.image' => 'アイキャッチ画像は画像ファイルを選択してください。',
            'eyecatch.max' => 'アイキャッチ画像は2MB以下にしてください。',
            'purpose.required' => '募集目的を選択してください。',
            'purpose.in' => '募集目的の選択が不正です。',
            'job_title.required' => 'やることを入力してください。',
            'job_title.max' => 'やることは50文字以内で入力してください。',
            'job_detail.required' => '具体的にはこんなことを手伝ってほしいを入力してください。',
            'job_detail.max' => '具体的にはこんなことを手伝ってほしいは200文字以内で入力してください。',
            'want_you_ids.*.exists' => '選択された希望が存在しません。',
            'can_do_ids.*.exists' => '選択されたできますが存在しません。',
        ];
    }
}
