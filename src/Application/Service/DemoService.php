<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Model\Demo;
use App\Domain\Port\DemoRepositoryInterface;

class DemoService
{
    public function __construct(
        private readonly DemoRepositoryInterface $demoRepository,
    ) {
    }

    public function find(int $id): ?Demo
    {
        return $this->demoRepository->findById($id);
    }

    /** @return Demo[] */
    public function findAll(): array
    {
        return $this->demoRepository->findAll();
    }

    public function create(string $name): Demo
    {
        $demo = new Demo($name);
        $this->demoRepository->save($demo);

        return $demo;
    }

    public function remove(Demo $demo): void
    {
        $this->demoRepository->remove($demo);
    }
}
