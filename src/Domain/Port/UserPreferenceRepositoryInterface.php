<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\User;
use App\Domain\Model\UserPreference;

interface UserPreferenceRepositoryInterface
{
    public function findByUserAndField(User $user, string $field): ?UserPreference;

    /** @return UserPreference[] */
    public function findByUser(User $user): array;

    public function save(UserPreference $preference): void;
}
