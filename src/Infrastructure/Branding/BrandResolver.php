<?php

declare(strict_types=1);

namespace App\Infrastructure\Branding;

use App\Domain\ValueObject\Brand;

class BrandResolver
{
    /** @param array<string, array{name: string}> $brandDefs */
    /** @param array<string, string> $brandMap */
    public function __construct(
        private readonly array $brandDefs,
        private readonly ?array $brandMap,
        private readonly string $defaultBrand,
    ) {
    }

    public function resolve(string $host): Brand
    {
        $key = ($this->brandMap ?? [])[$host] ?? $this->defaultBrand;
        $def = $this->brandDefs[$key] ?? $this->brandDefs[$this->defaultBrand];

        return new Brand(
            key: $key,
            name: $def['name'] ?? $key,
        );
    }
}
