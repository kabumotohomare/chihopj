<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ワーカープロフィール更新リクエスト
 */
class UpdateWorkerProfileRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'handle_name' => ['required', 'string', 'max:50'],
            'icon' => ['nullable', 'image', 'max:2048'], // 2MB
            'gender' => ['required', 'in:male,female,other'],
            'birthYear' => ['required', 'integer', 'min:'.(now()->year - 80), 'max:'.(now()->year - 18)],
            'birthMonth' => ['required', 'integer', 'min:1', 'max:12'],
            'birthDay' => ['required', 'integer', 'min:1', 'max:31'],
            'experiences' => ['nullable', 'string', 'max:200'],
            'want_to_do' => ['nullable', 'string', 'max:200'],
            'good_contribution' => ['nullable', 'string', 'max:200'],
            'birth_prefecture_id' => ['required', 'exists:locations,id'],
            'birth_location_id' => ['required', 'exists:locations,id'],
            'current_prefecture_1_id' => ['required', 'exists:locations,id'],
            'current_location_1_id' => ['required', 'exists:locations,id'],
            'current_prefecture_2_id' => ['nullable', 'exists:locations,id'],
            'current_location_2_id' => ['nullable', 'exists:locations,id'],
            'favorite_prefecture_1_id' => ['nullable', 'exists:locations,id'],
            'favorite_location_1_id' => ['nullable', 'exists:locations,id'],
            'favorite_prefecture_2_id' => ['nullable', 'exists:locations,id'],
            'favorite_location_2_id' => ['nullable', 'exists:locations,id'],
            'favorite_prefecture_3_id' => ['nullable', 'exists:locations,id'],
            'favorite_location_3_id' => ['nullable', 'exists:locations,id'],
            'available_action' => ['nullable', 'array'],
            'available_action.*' => ['string', 'in:mowing,snowplow,diy,localcleaning,volunteer'],
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
            'handle_name.required' => 'ハンドルネームは必須です。',
            'handle_name.max' => 'ハンドルネームは50文字以内で入力してください。',
            'icon.image' => 'アイコンは画像ファイルを選択してください。',
            'icon.max' => 'アイコンのファイルサイズは2MB以下にしてください。',
            'gender.required' => '性別は必須です。',
            'gender.in' => '性別が不正です。',
            'birthYear.required' => '生年月日（年）は必須です。',
            'birthYear.integer' => '生年月日（年）は数値で入力してください。',
            'birthYear.min' => '生年月日（年）は'.(now()->year - 80).'年以降を選択してください。',
            'birthYear.max' => '生年月日（年）は'.(now()->year - 18).'年以前を選択してください。',
            'birthMonth.required' => '生年月日（月）は必須です。',
            'birthMonth.integer' => '生年月日（月）は数値で入力してください。',
            'birthMonth.min' => '生年月日（月）は1〜12の範囲で選択してください。',
            'birthMonth.max' => '生年月日（月）は1〜12の範囲で選択してください。',
            'birthDay.required' => '生年月日（日）は必須です。',
            'birthDay.integer' => '生年月日（日）は数値で入力してください。',
            'birthDay.min' => '生年月日（日）は1〜31の範囲で選択してください。',
            'birthDay.max' => '生年月日（日）は1〜31の範囲で選択してください。',
            'experiences.max' => 'これまでの経験は200文字以内で入力してください。',
            'want_to_do.max' => 'これからやりたいことは200文字以内で入力してください。',
            'good_contribution.max' => '得意なことや貢献できることは200文字以内で入力してください。',
            'birth_prefecture_id.required' => '出身地（都道府県）は必須です。',
            'birth_prefecture_id.exists' => '出身地（都道府県）が不正です。',
            'birth_location_id.required' => '出身地（市区町村）は必須です。',
            'birth_location_id.exists' => '出身地（市区町村）が不正です。',
            'current_prefecture_1_id.required' => '現在のお住まい1（都道府県）は必須です。',
            'current_prefecture_1_id.exists' => '現在のお住まい1（都道府県）が不正です。',
            'current_location_1_id.required' => '現在のお住まい1（市区町村）は必須です。',
            'current_location_1_id.exists' => '現在のお住まい1（市区町村）が不正です。',
            'current_prefecture_2_id.exists' => '現在のお住まい2（都道府県）が不正です。',
            'current_location_2_id.exists' => '現在のお住まい2（市区町村）が不正です。',
            'favorite_prefecture_1_id.exists' => '移住に関心のある地域1（都道府県）が不正です。',
            'favorite_location_1_id.exists' => '移住に関心のある地域1（市区町村）が不正です。',
            'favorite_prefecture_2_id.exists' => '移住に関心のある地域2（都道府県）が不正です。',
            'favorite_location_2_id.exists' => '移住に関心のある地域2（市区町村）が不正です。',
            'favorite_prefecture_3_id.exists' => '移住に関心のある地域3（都道府県）が不正です。',
            'favorite_location_3_id.exists' => '移住に関心のある地域3（市区町村）が不正です。',
            'available_action.array' => '興味のあるお手伝いが不正です。',
            'available_action.*.in' => '興味のあるお手伝いに不正な値が含まれています。',
        ];
    }
}
