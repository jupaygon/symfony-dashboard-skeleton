<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Branding;

use App\Infrastructure\Branding\BrandResolver;
use PHPUnit\Framework\TestCase;

final class BrandResolverTest extends TestCase
{
    private function createResolver(array $devSuffixes = []): BrandResolver
    {
        return new BrandResolver(
            brandDefs: [
                'default' => ['name' => 'Default', 'menu' => 'sidebar'],
                'jarvis'  => ['name' => 'Jarvis', 'menu' => 'sidebar'],
                'topnav'  => ['name' => 'Top Nav', 'menu' => 'topnav'],
                'watson'  => ['name' => 'Watson', 'menu' => 'topnav'],
            ],
            brandMap: [
                'dashboard.example.com' => 'default',
                'dark.example.com'      => 'jarvis',
                'topnav.example.com'    => 'topnav',
            ],
            defaultBrand: 'default',
            devSuffixes: $devSuffixes,
        );
    }

    public function testResolveByDirectHostnameMatch(): void
    {
        $brand = $this->createResolver()->resolve('dark.example.com');

        self::assertSame('jarvis', $brand->getKey());
        self::assertSame('Jarvis', $brand->getName());
        self::assertSame('sidebar', $brand->getMenu());
    }

    public function testFallsBackToDefaultWhenHostnameNotMapped(): void
    {
        $brand = $this->createResolver()->resolve('unknown.example.com');

        self::assertSame('default', $brand->getKey());
    }

    public function testDevSuffixMatchStripsWorktreePrefix(): void
    {
        $resolver = $this->createResolver(devSuffixes: ['example.com']);

        $brand = $resolver->resolve('wt-my-project.example.com');

        // example.com is not in brandMap, so falls back to default
        self::assertSame('default', $brand->getKey());
    }

    public function testDevSuffixMatchResolvesToMappedBrand(): void
    {
        // Map the suffix itself to a brand
        $resolver = new BrandResolver(
            brandDefs: [
                'default' => ['name' => 'Default', 'menu' => 'sidebar'],
                'jarvis'  => ['name' => 'Jarvis', 'menu' => 'sidebar'],
            ],
            brandMap: [
                'my-app.test' => 'jarvis',
            ],
            defaultBrand: 'default',
            devSuffixes: ['my-app.test'],
        );

        $brand = $resolver->resolve('wt-feature-branch.my-app.test');

        self::assertSame('jarvis', $brand->getKey());
    }

    public function testResolveByKeyReturnsNullForUnknownKey(): void
    {
        $brand = $this->createResolver()->resolveByKey('nonexistent');

        self::assertNull($brand);
    }

    public function testResolveByKeyReturnsBrand(): void
    {
        $brand = $this->createResolver()->resolveByKey('watson');

        self::assertNotNull($brand);
        self::assertSame('watson', $brand->getKey());
        self::assertTrue($brand->isTopnav());
    }

    public function testResolveTopnavBrand(): void
    {
        $brand = $this->createResolver()->resolve('topnav.example.com');

        self::assertSame('topnav', $brand->getKey());
        self::assertTrue($brand->isTopnav());
    }

    public function testNullBrandMapFallsToDefault(): void
    {
        $resolver = new BrandResolver(
            brandDefs: ['default' => ['name' => 'Default', 'menu' => 'sidebar']],
            brandMap: null,
            defaultBrand: 'default',
        );

        $brand = $resolver->resolve('anything.com');

        self::assertSame('default', $brand->getKey());
    }
}
