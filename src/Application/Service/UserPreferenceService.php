<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Model\User;
use App\Domain\Model\UserPreference;
use App\Domain\Port\UserPreferenceRepositoryInterface;

class UserPreferenceService
{
    /** @param array<string, array{type: string, default: mixed, label: string}> $definitions */
    public function __construct(
        private readonly UserPreferenceRepositoryInterface $repository,
        private readonly array $definitions,
    ) {
    }

    public function get(User $user, string $field): mixed
    {
        $pref = $this->repository->findByUserAndField($user, $field);
        $default = $this->definitions[$field]['default'] ?? null;

        if ($pref === null) {
            return $default;
        }

        return $this->cast($pref->getValue(), $this->definitions[$field]['type'] ?? 'string');
    }

    public function set(User $user, string $field, mixed $value): void
    {
        if (!isset($this->definitions[$field])) {
            throw new \InvalidArgumentException(sprintf('Unknown preference field: %s', $field));
        }

        $pref = $this->repository->findByUserAndField($user, $field);

        if ($pref === null) {
            $pref = new UserPreference($user, $field, (string) $value);
        } else {
            $pref->setValue((string) $value);
        }

        $this->repository->save($pref);
    }

    public function toggle(User $user, string $field): bool
    {
        $current = $this->get($user, $field);
        $new = !$current;
        $this->set($user, $field, $new ? '1' : '0');

        return $new;
    }

    /** @return array<string, array{type: string, default: mixed, label: string}> */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    private function cast(string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => in_array($value, ['1', 'true', 'yes'], true),
            'integer' => (int) $value,
            default => $value,
        };
    }
}
