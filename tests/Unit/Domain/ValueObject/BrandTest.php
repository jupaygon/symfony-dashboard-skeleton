<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Brand;
use PHPUnit\Framework\TestCase;

final class BrandTest extends TestCase
{
    public function testDefaultMenuIsSidebar(): void
    {
        $brand = new Brand(key: 'test', name: 'Test');

        self::assertSame('sidebar', $brand->getMenu());
        self::assertFalse($brand->isTopnav());
    }

    public function testTopnavMenu(): void
    {
        $brand = new Brand(key: 'watson', name: 'Watson', menu: 'topnav');

        self::assertSame('topnav', $brand->getMenu());
        self::assertTrue($brand->isTopnav());
    }

    public function testGetters(): void
    {
        $brand = new Brand(key: 'jarvis', name: 'Jarvis Dark');

        self::assertSame('jarvis', $brand->getKey());
        self::assertSame('Jarvis Dark', $brand->getName());
    }
}
