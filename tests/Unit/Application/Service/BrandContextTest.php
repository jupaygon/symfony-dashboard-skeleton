<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\BrandContext;
use App\Domain\ValueObject\Brand;
use PHPUnit\Framework\TestCase;

final class BrandContextTest extends TestCase
{
    public function testGetThrowsWhenBrandNotSet(): void
    {
        $context = new BrandContext();

        $this->expectException(\LogicException::class);
        $context->get();
    }

    public function testSetAndGet(): void
    {
        $context = new BrandContext();
        $brand = new Brand(key: 'jarvis', name: 'Jarvis');

        $context->set($brand);

        self::assertSame($brand, $context->get());
    }
}
