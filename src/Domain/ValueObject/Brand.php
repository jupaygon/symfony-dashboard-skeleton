<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Contract\BrandInterface;

final readonly class Brand implements BrandInterface
{
    public function __construct(
        private string $key,
        private string $name,
        private bool $sidebarCollapsed = false,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSidebarCollapsed(): bool
    {
        return $this->sidebarCollapsed;
    }
}
