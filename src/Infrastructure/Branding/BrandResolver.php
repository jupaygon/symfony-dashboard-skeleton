<?php

declare(strict_types=1);

namespace App\Infrastructure\Branding;

use App\Domain\ValueObject\Brand;

readonly class BrandResolver
{
    /** @param array<string, array{name: string}> $brandDefs */
    /** @param array<string, string>|null $brandMap */
    /** @param string[] $devSuffixes */
    public function __construct(
        private array $brandDefs,
        private ?array $brandMap,
        private string $defaultBrand,
        private array $devSuffixes = [],
    ) {
    }

    public function resolveByKey(string $key): ?Brand
    {
        $def = $this->brandDefs[$key] ?? null;

        if ($def === null) {
            return null;
        }

        return new Brand(
            key: $key,
            name: $def['name'] ?? $key,
            menu: $def['menu'] ?? 'sidebar',
            dark: $def['dark'] ?? false,
        );
    }

    public function resolve(string $host): Brand
    {
        $map = $this->brandMap ?? [];

        // Direct match
        $key = $map[$host] ?? null;

        // Dev suffix match: strip worktree prefix and try the suffix as hostname
        if ($key === null && !empty($this->devSuffixes)) {
            foreach ($this->devSuffixes as $suffix) {
                if (str_ends_with($host, '.' . $suffix)) {
                    $key = $map[$suffix] ?? null;
                    if ($key !== null) {
                        break;
                    }
                }
            }
        }

        $key = $key ?? $this->defaultBrand;
        $def = $this->brandDefs[$key] ?? $this->brandDefs[$this->defaultBrand];

        return new Brand(
            key: $key,
            name: $def['name'] ?? $key,
            menu: $def['menu'] ?? 'sidebar',
            dark: $def['dark'] ?? false,
        );
    }
}
