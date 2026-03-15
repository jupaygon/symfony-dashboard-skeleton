<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface BrandInterface
{
    public function getKey(): string;

    public function getName(): string;
}
