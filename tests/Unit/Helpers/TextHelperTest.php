<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\TextHelper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TextHelperクラスのテスト
 */
class TextHelperTest extends TestCase
{
    #[Test]
    public function it_converts_urls_to_links(): void
    {
        $text = 'サイトはこちら https://example.com です。';
        $result = TextHelper::autoLink($text);

        $this->assertStringContainsString('<a href="https://example.com"', $result);
        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);
        $this->assertStringContainsString('https://example.com</a>', $result);
    }

    #[Test]
    public function it_converts_multiple_urls_to_links(): void
    {
        $text = 'サイト1: https://example.com サイト2: https://example.org';
        $result = TextHelper::autoLink($text);

        $this->assertStringContainsString('https://example.com</a>', $result);
        $this->assertStringContainsString('https://example.org</a>', $result);
    }

    #[Test]
    public function it_handles_urls_with_punctuation(): void
    {
        $text = 'サイトはhttps://example.com、こちらです。';
        $result = TextHelper::autoLink($text);

        // URLの直後の句読点は含まれない
        $this->assertStringContainsString('https://example.com</a>、', $result);
    }

    #[Test]
    public function it_escapes_html_in_text(): void
    {
        $text = '<script>alert("XSS")</script>';
        $result = TextHelper::autoLink($text);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    #[Test]
    public function it_converts_newlines_to_br_tags(): void
    {
        $text = "1行目\n2行目";
        $result = TextHelper::autoLink($text);

        $this->assertStringContainsString('<br', $result);
    }

    #[Test]
    public function it_handles_http_and_https_urls(): void
    {
        $text = 'HTTP: http://example.com HTTPS: https://example.org';
        $result = TextHelper::autoLink($text);

        $this->assertStringContainsString('http://example.com</a>', $result);
        $this->assertStringContainsString('https://example.org</a>', $result);
    }

    #[Test]
    public function it_returns_text_without_urls_unchanged(): void
    {
        $text = 'URLを含まないテキストです。';
        $result = TextHelper::autoLink($text);

        $this->assertStringContainsString('URLを含まないテキストです。', $result);
        $this->assertStringNotContainsString('<a href=', $result);
    }
}
