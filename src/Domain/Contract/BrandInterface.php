<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface BrandInterface
{
    public function getKey(): string;

    public function getName(): string;

    public function getMenu(): string;

    public function isTopnav(): bool;

    public function isDark(): bool;
}
