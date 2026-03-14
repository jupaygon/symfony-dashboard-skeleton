<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Demo;

interface DemoRepositoryInterface
{
    public function findById(int $id): ?Demo;

    /** @return Demo[] */
    public function findAll(): array;

    public function save(Demo $demo): void;

    public function remove(Demo $demo): void;
}
