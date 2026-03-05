<?php

/**
 * バリデーションメッセージ日本語化
 *
 * 修正:WinLogic - バリデーションエラーが「The email field is required.」等の英語で表示されていたため新規作成。
 * 全バリデーションルールを日本語に翻訳。attributes にフィールド名の日本語マッピング（60件）を定義。
 *
 * @see https://laravel.com/docs/validation
 */
return [

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeは:dateより後の日付にしてください。',
    'after_or_equal' => ':attributeは:date以降の日付にしてください。',
    'alpha' => ':attributeは英字のみで入力してください。',
    'alpha_dash' => ':attributeは英数字、ハイフン、アンダースコアのみで入力してください。',
    'alpha_num' => ':attributeは英数字のみで入力してください。',
    'array' => ':attributeは配列形式にしてください。',
    'ascii' => ':attributeはASCII文字のみで入力してください。',
    'before' => ':attributeは:dateより前の日付にしてください。',
    'before_or_equal' => ':attributeは:date以前の日付にしてください。',
    'between' => [
        'array' => ':attributeは:min〜:max個にしてください。',
        'file' => ':attributeは:min〜:maxキロバイトにしてください。',
        'numeric' => ':attributeは:min〜:maxの範囲で入力してください。',
        'string' => ':attributeは:min〜:max文字で入力してください。',
    ],
    'boolean' => ':attributeはtrueまたはfalseにしてください。',
    'can' => ':attributeに許可されていない値が含まれています。',
    'confirmed' => ':attributeが確認用と一致しません。',
    'contains' => ':attributeに必須の値が含まれていません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは有効な日付ではありません。',
    'date_equals' => ':attributeは:dateと同じ日付にしてください。',
    'date_format' => ':attributeは:format形式で入力してください。',
    'decimal' => ':attributeは小数点以下:decimal桁にしてください。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeと:otherは異なる値にしてください。',
    'digits' => ':attributeは:digits桁の数字で入力してください。',
    'digits_between' => ':attributeは:min〜:max桁の数字で入力してください。',
    'dimensions' => ':attributeの画像サイズが無効です。',
    'distinct' => ':attributeに重複した値があります。',
    'doesnt_end_with' => ':attributeは:valuesで終わらないようにしてください。',
    'doesnt_start_with' => ':attributeは:valuesで始まらないようにしてください。',
    'email' => ':attributeは有効なメールアドレスを入力してください。',
    'ends_with' => ':attributeは:valuesのいずれかで終わる必要があります。',
    'enum' => '選択された:attributeは無効です。',
    'exists' => '選択された:attributeは無効です。',
    'extensions' => ':attributeは:valuesの拡張子にしてください。',
    'file' => ':attributeはファイルにしてください。',
    'filled' => ':attributeは必須です。',
    'gt' => [
        'array' => ':attributeは:value個より多くしてください。',
        'file' => ':attributeは:valueキロバイトより大きくしてください。',
        'numeric' => ':attributeは:valueより大きい値にしてください。',
        'string' => ':attributeは:value文字より長くしてください。',
    ],
    'gte' => [
        'array' => ':attributeは:value個以上にしてください。',
        'file' => ':attributeは:valueキロバイト以上にしてください。',
        'numeric' => ':attributeは:value以上にしてください。',
        'string' => ':attributeは:value文字以上にしてください。',
    ],
    'hex_color' => ':attributeは有効な16進カラーコードにしてください。',
    'image' => ':attributeは画像ファイルにしてください。',
    'in' => '選択された:attributeは無効です。',
    'in_array' => ':attributeは:otherに存在しません。',
    'integer' => ':attributeは整数で入力してください。',
    'ip' => ':attributeは有効なIPアドレスにしてください。',
    'ipv4' => ':attributeは有効なIPv4アドレスにしてください。',
    'ipv6' => ':attributeは有効なIPv6アドレスにしてください。',
    'json' => ':attributeは有効なJSON文字列にしてください。',
    'list' => ':attributeはリスト形式にしてください。',
    'lowercase' => ':attributeは小文字で入力してください。',
    'lt' => [
        'array' => ':attributeは:value個より少なくしてください。',
        'file' => ':attributeは:valueキロバイトより小さくしてください。',
        'numeric' => ':attributeは:valueより小さい値にしてください。',
        'string' => ':attributeは:value文字より短くしてください。',
    ],
    'lte' => [
        'array' => ':attributeは:value個以下にしてください。',
        'file' => ':attributeは:valueキロバイト以下にしてください。',
        'numeric' => ':attributeは:value以下にしてください。',
        'string' => ':attributeは:value文字以下にしてください。',
    ],
    'mac_address' => ':attributeは有効なMACアドレスにしてください。',
    'max' => [
        'array' => ':attributeは:max個以下にしてください。',
        'file' => ':attributeは:maxキロバイト以下にしてください。',
        'numeric' => ':attributeは:max以下にしてください。',
        'string' => ':attributeは:max文字以下で入力してください。',
    ],
    'max_digits' => ':attributeは:max桁以下にしてください。',
    'mimes' => ':attributeは:valuesタイプのファイルにしてください。',
    'mimetypes' => ':attributeは:valuesタイプのファイルにしてください。',
    'min' => [
        'array' => ':attributeは:min個以上にしてください。',
        'file' => ':attributeは:minキロバイト以上にしてください。',
        'numeric' => ':attributeは:min以上にしてください。',
        'string' => ':attributeは:min文字以上で入力してください。',
    ],
    'min_digits' => ':attributeは:min桁以上にしてください。',
    'missing' => ':attributeは存在してはなりません。',
    'missing_if' => ':otherが:valueの場合、:attributeは存在してはなりません。',
    'missing_unless' => ':otherが:valueでない場合、:attributeは存在してはなりません。',
    'missing_with' => ':valuesが存在する場合、:attributeは存在してはなりません。',
    'missing_with_all' => ':valuesがすべて存在する場合、:attributeは存在してはなりません。',
    'multiple_of' => ':attributeは:valueの倍数にしてください。',
    'not_in' => '選択された:attributeは無効です。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeは数値で入力してください。',
    'password' => [
        'letters' => ':attributeは1文字以上の英字を含めてください。',
        'mixed' => ':attributeは大文字と小文字をそれぞれ1文字以上含めてください。',
        'numbers' => ':attributeは1文字以上の数字を含めてください。',
        'symbols' => ':attributeは1文字以上の記号を含めてください。',
        'uncompromised' => ':attributeは漏洩したデータに含まれています。別の値を入力してください。',
    ],
    'present' => ':attributeが存在する必要があります。',
    'present_if' => ':otherが:valueの場合、:attributeが存在する必要があります。',
    'present_unless' => ':otherが:valueでない場合、:attributeが存在する必要があります。',
    'present_with' => ':valuesが存在する場合、:attributeが存在する必要があります。',
    'present_with_all' => ':valuesがすべて存在する場合、:attributeが存在する必要があります。',
    'prohibited' => ':attributeは入力禁止です。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは入力禁止です。',
    'prohibited_unless' => ':otherが:valuesに含まれない場合、:attributeは入力禁止です。',
    'prohibits' => ':attributeが存在する場合、:otherは入力禁止です。',
    'regex' => ':attributeの形式が無効です。',
    'required' => ':attributeは必須です。',
    'required_array_keys' => ':attributeには:valuesのキーが必要です。',
    'required_if' => ':otherが:valueの場合、:attributeは必須です。',
    'required_if_accepted' => ':otherが承認されている場合、:attributeは必須です。',
    'required_if_declined' => ':otherが拒否されている場合、:attributeは必須です。',
    'required_unless' => ':otherが:valuesに含まれない場合、:attributeは必須です。',
    'required_with' => ':valuesが存在する場合、:attributeは必須です。',
    'required_with_all' => ':valuesがすべて存在する場合、:attributeは必須です。',
    'required_without' => ':valuesが存在しない場合、:attributeは必須です。',
    'required_without_all' => ':valuesがいずれも存在しない場合、:attributeは必須です。',
    'same' => ':attributeと:otherは同じ値にしてください。',
    'size' => [
        'array' => ':attributeは:size個にしてください。',
        'file' => ':attributeは:sizeキロバイトにしてください。',
        'numeric' => ':attributeは:sizeにしてください。',
        'string' => ':attributeは:size文字にしてください。',
    ],
    'starts_with' => ':attributeは:valuesのいずれかで始まる必要があります。',
    'string' => ':attributeは文字列にしてください。',
    'timezone' => ':attributeは有効なタイムゾーンにしてください。',
    'unique' => ':attributeはすでに使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは大文字で入力してください。',
    'url' => ':attributeは有効なURLを入力してください。',
    'ulid' => ':attributeは有効なULIDにしてください。',
    'uuid' => ':attributeは有効なUUIDにしてください。',

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーションメッセージ
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | カスタム属性名
    |--------------------------------------------------------------------------
    |
    | :attribute プレースホルダーをフィールドの日本語名に置換。
    |
    */

    'attributes' => [
        // 認証関連
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'current_password' => '現在のパスワード',
        'remember' => 'ログイン情報を保持',
        'role' => '役割',
        'code' => '認証コード',
        'recovery_code' => 'リカバリーコード',
        'token' => 'トークン',

        // ユーザー・プロフィール共通
        'name' => '名前',
        'handle_name' => 'ハンドルネーム',
        'gender' => '性別',
        'icon' => 'アイコン画像',
        'phone_number' => '電話番号',
        'message' => 'メッセージ',
        'agree_to_terms' => '利用規約への同意',

        // 生年月日
        'birth_year' => '生年',
        'birth_month' => '生月',
        'birth_day' => '生日',
        'birthYear' => '生年',
        'birthMonth' => '生月',
        'birthDay' => '生日',

        // 住所・地域
        'address' => '住所',
        'current_address' => '現住所',
        'birth_prefecture_id' => '出身都道府県',
        'birth_location_id' => '出身市区町村',
        'current_prefecture_1_id' => '現在の都道府県（1）',
        'current_prefecture_2_id' => '現在の都道府県（2）',
        'current_location_1_id' => '現在の市区町村（1）',
        'current_location_2_id' => '現在の市区町村（2）',

        // 事業者（ホスト）関連
        'representative' => '代表者名',

        // 募集関連
        'job_title' => '募集タイトル',
        'job_detail' => '募集内容',
        'purpose' => '募集目的',
        'location' => '活動場所',
        'eyecatch' => 'アイキャッチ画像',
        'start_datetime' => '開始日時',
        'end_datetime' => '終了日時',
        'want_you_ids' => '希望タグ',
        'want_you_ids.*' => '希望タグ',
        'can_do_ids' => 'できますタグ',
        'can_do_ids.*' => 'できますタグ',

        // 応募関連
        'reasons' => '気になる点',
        'reasons.*' => '気になる点',
        'motive' => '応募メッセージ',
    ],

];
