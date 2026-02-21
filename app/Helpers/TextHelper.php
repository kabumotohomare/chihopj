<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * テキスト処理ヘルパークラス
 */
class TextHelper
{
    /**
     * テキスト内のURLを自動的にリンクに変換
     *
     * @param  string  $text  変換対象のテキスト
     * @return string リンク化されたHTML
     */
    public static function autoLink(string $text): string
    {
        // HTMLエスケープ
        $text = e($text);

        // URLパターンにマッチする正規表現
        $pattern = '/(https?:\/\/[^\s　<>"\']+)/i';

        // URLをリンクに置換
        $text = preg_replace_callback($pattern, function ($matches) {
            $url = $matches[1];

            // URLの末尾の句読点を除外
            $punctuation = '';
            if (preg_match('/([,.、。!?！？\)）]+)$/', $url, $punctMatches)) {
                $punctuation = $punctMatches[1];
                $url = substr($url, 0, -strlen($punctuation));
            }

            // リンクを生成（新しいタブで開く、noopener noreferrer付き、break-words適用）
            return sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline break-all dark:text-blue-400 dark:hover:text-blue-300">%s</a>%s',
                $url,
                $url,
                $punctuation
            );
        }, $text);

        // 改行をbrタグに変換
        $text = nl2br($text);

        return $text;
    }
}
