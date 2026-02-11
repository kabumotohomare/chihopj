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
     * バリデーション前にデータを準備
     */
    protected function prepareForValidation(): void
    {
        // purposeがwant_to_doの場合、日時フィールドをnullに設定
        if ($this->purpose === 'want_to_do') {
            $this->merge([
                'start_datetime' => null,
                'end_datetime' => null,
            ]);
        }

        // 空文字列をnullに変換
        if ($this->has('start_datetime') && $this->start_datetime === '') {
            $this->merge(['start_datetime' => null]);
        }
        if ($this->has('end_datetime') && $this->end_datetime === '') {
            $this->merge(['end_datetime' => null]);
        }
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
            'start_datetime' => [
                'nullable',
                'required_if:purpose,need_help',
                function ($attribute, $value, $fail) {
                    // 空文字列の場合はスキップ
                    if ($value === '' || $value === null) {
                        return;
                    }
                    // 日時形式のバリデーション
                    if (! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
                        $fail('開始日時は正しい日時形式で入力してください。');

                        return;
                    }
                    // 今日以降の日時チェック
                    if (strtotime($value) < strtotime('today')) {
                        $fail('開始日時は今日以降の日時を選択してください。');
                    }
                },
            ],
            'end_datetime' => [
                'nullable',
                'required_if:purpose,need_help',
                function ($attribute, $value, $fail) {
                    // 空文字列の場合はスキップ
                    if ($value === '' || $value === null) {
                        return;
                    }
                    // 日時形式のバリデーション
                    if (! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
                        $fail('終了日時は正しい日時形式で入力してください。');

                        return;
                    }
                    // 開始日時より後の日時チェック
                    if ($this->start_datetime && strtotime($value) <= strtotime($this->start_datetime)) {
                        $fail('終了日時は開始日時より後の日時を選択してください。');
                    }
                },
            ],
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
            'start_datetime.required_if' => '決まった日に募集の場合、開始日時を入力してください。',
            'end_datetime.required_if' => '決まった日に募集の場合、終了日時を入力してください。',
            'job_title.required' => 'やることを入力してください。',
            'job_title.max' => 'やることは50文字以内で入力してください。',
            'job_detail.required' => '具体的にはこんなことを手伝ってほしいを入力してください。',
            'job_detail.max' => '具体的にはこんなことを手伝ってほしいは200文字以内で入力してください。',
            'want_you_ids.*.exists' => '選択された希望が存在しません。',
            'can_do_ids.*.exists' => '選択されたできますが存在しません。',
        ];
    }
}
