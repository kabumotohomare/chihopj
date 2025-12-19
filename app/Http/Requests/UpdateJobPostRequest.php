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
            'howsoon' => ['required', 'in:someday,asap,specific_month'],
            'job_title' => ['required', 'string', 'max:50'],
            'job_detail' => ['required', 'string', 'max:200'],
            'job_type_id' => ['required', 'integer', 'exists:codes,id'],
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
            'howsoon.required' => 'いつまでにを選択してください。',
            'howsoon.in' => 'いつまでにの選択が不正です。',
            'job_title.required' => 'やりたいことを入力してください。',
            'job_title.max' => 'やりたいことは50文字以内で入力してください。',
            'job_detail.required' => '事業内容・困っていることを入力してください。',
            'job_detail.max' => '事業内容・困っていることは200文字以内で入力してください。',
            'job_type_id.required' => '募集形態を選択してください。',
            'job_type_id.exists' => '選択された募集形態が存在しません。',
            'want_you_ids.*.exists' => '選択された希望が存在しません。',
            'can_do_ids.*.exists' => '選択されたできますが存在しません。',
        ];
    }

    /**
     * カスタムバリデーション（プロプラン機能の制限）
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('howsoon') === 'specific_month') {
                $validator->errors()->add(
                    'howsoon',
                    '「◯月までに」を選択するにはプロプランに変更してください。'
                );
            }
        });
    }
}
