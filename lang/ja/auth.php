<?php

/**
 * 認証メッセージ日本語化
 *
 * 修正:WinLogic - APP_LOCALE=ja 設定時に lang/ja/ が存在せず、ログイン失敗時に
 * 「These credentials do not match our records.」と英語メッセージが表示されていたため新規作成。
 *
 * @see https://laravel.com/docs/localization
 */
return [

    'failed' => '認証に失敗しました。入力情報を確認してください。',
    'password' => 'パスワードが正しくありません。',
    'throttle' => 'ログイン試行回数が多すぎます。:seconds 秒後に再試行してください。',

];
