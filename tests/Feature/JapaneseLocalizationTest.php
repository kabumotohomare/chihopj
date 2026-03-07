<?php

/**
 * バリデーション・認証メッセージ日本語化テスト
 *
 * 修正:WinLogic - 日本語翻訳ファイル（lang/ja/）追加に伴い、
 * 各種エラーメッセージが日本語で表示されることを検証するテストを新規作成。
 */

use App\Models\User;

test('ログイン失敗時に日本語の認証エラーメッセージが表示される', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHas('errors');

    $errors = session('errors');
    $errorMessages = $errors->all();
    $hasJapanese = collect($errorMessages)->contains(fn ($message) => str_contains($message, '認証に失敗しました'));

    expect($hasJapanese)->toBeTrue();
});

test('バリデーション必須エラーが日本語で表示される', function () {
    $response = $this->post(route('register.store'), [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => '',
    ]);

    $response->assertSessionHasErrors();

    $errors = session('errors');
    $emailError = $errors->first('email');

    // :attribute が日本語名に置換され、日本語メッセージが表示される
    expect($emailError)->toContain('メールアドレス');
    expect($emailError)->toContain('必須');
});

test('パスワードリセットで存在しないメールアドレスに日本語メッセージが返る', function () {
    $response = $this->post(route('password.request'), [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertSessionHas('errors');

    $errors = session('errors');
    $emailError = $errors->first('email');

    expect($emailError)->toContain('メールアドレス');
});

test('lang/ja/auth.php が正しいキーを含む', function () {
    $auth = require lang_path('ja/auth.php');

    expect($auth)->toHaveKeys(['failed', 'password', 'throttle']);
    expect($auth['failed'])->toContain('認証に失敗しました');
    expect($auth['throttle'])->toContain(':seconds');
});

test('lang/ja/validation.php が正しいキーと属性名を含む', function () {
    $validation = require lang_path('ja/validation.php');

    expect($validation)->toHaveKeys(['required', 'email', 'max', 'min', 'confirmed', 'attributes']);
    expect($validation['attributes'])->toHaveKeys(['email', 'password', 'handle_name', 'name']);
    expect($validation['attributes']['email'])->toBe('メールアドレス');
    expect($validation['attributes']['handle_name'])->toBe('ハンドルネーム');
});

test('lang/ja/passwords.php が正しいキーを含む', function () {
    $passwords = require lang_path('ja/passwords.php');

    expect($passwords)->toHaveKeys(['reset', 'sent', 'throttled', 'token', 'user']);
    expect($passwords['sent'])->toContain('メール');
});

test('lang/ja/pagination.php が正しいキーを含む', function () {
    $pagination = require lang_path('ja/pagination.php');

    expect($pagination)->toHaveKeys(['previous', 'next']);
    expect($pagination['next'])->toContain('次へ');
});
