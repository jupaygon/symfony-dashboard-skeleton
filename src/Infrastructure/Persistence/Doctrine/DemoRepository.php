<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Model\Demo;
use App\Domain\Port\DemoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Demo>
 */
class DemoRepository extends ServiceEntityRepository implements DemoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demo::class);
    }

    public function findById(int $id): ?Demo
    {
        return $this->find($id);
    }

    public function save(Demo $demo): void
    {
        $this->getEntityManager()->persist($demo);
        $this->getEntityManager()->flush();
    }

    public function remove(Demo $demo): void
    {
        $this->getEntityManager()->remove($demo);
        $this->getEntityManager()->flush();
    }
}
