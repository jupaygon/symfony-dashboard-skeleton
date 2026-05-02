<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface
{
    public function findById(Uuid|string $id): ?User;

    public function findByEmail(string $email): ?User;

    /** @return User[] */
    public function findAll(): array;

    public function save(User $user): void;

    public function remove(User $user): void;

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void;
}
