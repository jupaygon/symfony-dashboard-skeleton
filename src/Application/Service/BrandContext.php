<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Contract\BrandInterface;

class BrandContext
{
    private ?BrandInterface $brand = null;

    public function set(BrandInterface $brand): void
    {
        $this->brand = $brand;
    }

    public function get(): BrandInterface
    {
        if (null === $this->brand) {
            throw new \LogicException('Brand not resolved. Is BrandResolverSubscriber registered?');
        }

        return $this->brand;
    }
}
