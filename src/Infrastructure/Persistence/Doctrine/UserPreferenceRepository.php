<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Model\User;
use App\Domain\Model\UserPreference;
use App\Domain\Port\UserPreferenceRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPreference>
 */
class UserPreferenceRepository extends ServiceEntityRepository implements UserPreferenceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPreference::class);
    }

    public function findByUserAndField(User $user, string $field): ?UserPreference
    {
        return $this->findOneBy(['user' => $user, 'field' => $field]);
    }

    /** @return UserPreference[] */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function save(UserPreference $preference): void
    {
        $this->getEntityManager()->persist($preference);
        $this->getEntityManager()->flush();
    }
}
