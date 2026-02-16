<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 募集投稿更新のバリデーションリクエスト
 */
class UpdateJobPostRequest extends FormRequest
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
            'start_datetime' => ['nullable', 'required_if:purpose,need_help', 'date', 'after_or_equal:today'],
            'end_datetime' => ['nullable', 'required_if:purpose,need_help', 'date', 'after:start_datetime'],
            'job_title' => ['required', 'string', 'max:50'],
            'job_detail' => ['required', 'string', 'max:200'],
            'location' => ['required', 'string', 'max:200'],
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
            'start_datetime.required_if' => '決まった日に募集の場合、開始日時を入力してください。',
            'start_datetime.date' => '開始日時は正しい日時形式で入力してください。',
            'start_datetime.after_or_equal' => '開始日時は今日以降の日時を選択してください。',
            'end_datetime.required_if' => '決まった日に募集の場合、終了日時を入力してください。',
            'end_datetime.date' => '終了日時は正しい日時形式で入力してください。',
            'end_datetime.after' => '終了日時は開始日時より後の日時を選択してください。',
            'job_title.required' => 'やることを入力してください。',
            'job_title.max' => 'やることは50文字以内で入力してください。',
            'job_detail.required' => '具体的にはこんなことを手伝ってほしいを入力してください。',
            'job_detail.max' => '具体的にはこんなことを手伝ってほしいは200文字以内で入力してください。',
            'location.required' => 'どこでを入力してください。',
            'location.max' => 'どこでは200文字以内で入力してください。',
            'want_you_ids.*.exists' => '選択された希望が存在しません。',
            'can_do_ids.*.exists' => '選択されたできますが存在しません。',
        ];
    }
}
