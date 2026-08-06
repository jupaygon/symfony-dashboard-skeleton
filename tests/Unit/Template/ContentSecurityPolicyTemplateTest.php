<?php

declare(strict_types=1);

namespace App\Tests\Unit\Template;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The CSP is enforced in every environment and EasyAdmin asks for a nonce, so the
 * browser stops honouring 'unsafe-inline'. An inline script without a nonce then
 * dies with no console error — these tests fail instead.
 */
class ContentSecurityPolicyTemplateTest extends TestCase
{
    private const TEMPLATES_DIR = __DIR__.'/../../../templates';

    /**
     * @return iterable<string, array{string}>
     */
    public static function templateProvider(): iterable
    {
        $root = realpath(self::TEMPLATES_DIR) ?: self::TEMPLATES_DIR;

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || 'twig' !== $file->getExtension()) {
                continue;
            }

            yield substr($file->getPathname(), \strlen($root) + 1) => [$file->getPathname()];
        }
    }

    #[DataProvider('templateProvider')]
    public function testInlineScriptsCarryTheCspNonce(string $path): void
    {
        preg_match_all('/<script(?![^>]*\bsrc=)[^>]*>/i', $this->read($path), $matches);

        $withoutNonce = array_values(array_filter(
            $matches[0],
            static fn (string $tag): bool => !str_contains($tag, 'nonce'),
        ));

        self::assertSame([], $withoutNonce, 'Inline <script> without a CSP nonce in '.basename($path));
    }

    #[DataProvider('templateProvider')]
    public function testNoInlineEventAttributes(string $path): void
    {
        // No nonce can cover on*="..." attributes; they need addEventListener.
        preg_match_all('/\son(?:click|input|change|submit|load|error|focus|blur|key\w+|mouse\w+)\s*=/i', $this->read($path), $matches);

        self::assertSame([], $matches[0], 'Inline event attribute in '.basename($path).' — bind it from a nonced <script> instead');
    }

    /**
     * Twig comments are stripped: they describe markup without rendering it.
     */
    private function read(string $path): string
    {
        $contents = file_get_contents($path);
        self::assertIsString($contents, "Unreadable template: $path");

        return (string) preg_replace('/\{#.*?#\}/s', '', $contents);
    }
}
