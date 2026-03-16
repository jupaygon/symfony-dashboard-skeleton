<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model;

use App\Domain\Model\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $user = new User();

        self::assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetRolesPreservesRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        self::assertContains('ROLE_ADMIN', $roles);
        self::assertContains('ROLE_USER', $roles);
    }

    public function testIsSuperAdmin(): void
    {
        $user = new User();
        self::assertFalse($user->isSuperAdmin());

        $user->setRoles([User::ROLE_SUPER_ADMIN]);
        self::assertTrue($user->isSuperAdmin());
    }

    public function testUserIdentifierIsEmail(): void
    {
        $user = new User();
        $user->setEmail('admin@example.com');

        self::assertSame('admin@example.com', $user->getUserIdentifier());
    }

    public function testToStringReturnsNameOrEmail(): void
    {
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setName('Admin User');

        self::assertSame('Admin User', (string) $user);
    }

    public function testCreatedAtIsSetOnConstruction(): void
    {
        $user = new User();

        self::assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testActiveByDefault(): void
    {
        $user = new User();

        self::assertTrue($user->isActive());
    }

    public function testRolesAreUnique(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_USER', 'ROLE_ADMIN']);

        $roles = $user->getRoles();

        self::assertCount(2, $roles);
    }
}
