<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\UserPreferenceService;
use App\Domain\Model\User;
use App\Domain\Model\UserPreference;
use App\Domain\Port\UserPreferenceRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class UserPreferenceServiceTest extends TestCase
{
    private const array DEFINITIONS = [
        'sidebar_collapsed' => ['type' => 'boolean', 'default' => false, 'label' => 'Sidebar collapsed'],
        'content_maximized' => ['type' => 'boolean', 'default' => true, 'label' => 'Content maximized'],
        'locale'            => ['type' => 'string', 'default' => 'en', 'label' => 'Language'],
    ];

    private function createService(?UserPreferenceRepositoryInterface $repo = null): UserPreferenceService
    {
        return new UserPreferenceService(
            $repo ?? $this->createMock(UserPreferenceRepositoryInterface::class),
            self::DEFINITIONS,
        );
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');

        return $user;
    }

    public function testGetReturnsDefaultWhenNoPreferenceStored(): void
    {
        $repo = $this->createMock(UserPreferenceRepositoryInterface::class);
        $repo->method('findByUserAndField')->willReturn(null);

        $service = $this->createService($repo);

        self::assertFalse($service->get($this->createUser(), 'sidebar_collapsed'));
        self::assertTrue($service->get($this->createUser(), 'content_maximized'));
        self::assertSame('en', $service->get($this->createUser(), 'locale'));
    }

    public function testGetReturnsStoredValue(): void
    {
        $user = $this->createUser();
        $pref = new UserPreference($user, 'sidebar_collapsed', '1');

        $repo = $this->createMock(UserPreferenceRepositoryInterface::class);
        $repo->method('findByUserAndField')->willReturn($pref);

        $service = $this->createService($repo);

        self::assertTrue($service->get($user, 'sidebar_collapsed'));
    }

    public function testGetCastsStringType(): void
    {
        $user = $this->createUser();
        $pref = new UserPreference($user, 'locale', 'es');

        $repo = $this->createMock(UserPreferenceRepositoryInterface::class);
        $repo->method('findByUserAndField')->willReturn($pref);

        $service = $this->createService($repo);

        self::assertSame('es', $service->get($user, 'locale'));
    }

    public function testSetThrowsForUnknownField(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $service->set($this->createUser(), 'nonexistent_field', 'value');
    }

    public function testSetCreatesNewPreference(): void
    {
        $user = $this->createUser();

        $repo = $this->createMock(UserPreferenceRepositoryInterface::class);
        $repo->method('findByUserAndField')->willReturn(null);
        $repo->expects(self::once())->method('save');

        $service = $this->createService($repo);
        $service->set($user, 'locale', 'es');
    }

    public function testSetUpdatesExistingPreference(): void
    {
        $user = $this->createUser();
        $pref = new UserPreference($user, 'locale', 'en');

        $repo = $this->createMock(UserPreferenceRepositoryInterface::class);
        $repo->method('findByUserAndField')->willReturn($pref);
        $repo->expects(self::once())->method('save');

        $service = $this->createService($repo);
        $service->set($user, 'locale', 'es');

        self::assertSame('es', $pref->getValue());
    }

    public function testToggleFlipsBooleanFromFalseToTrue(): void
    {
        $user = $this->createUser();

        $repo = $this->createMock(UserPreferenceRepositoryInterface::class);
        $repo->method('findByUserAndField')->willReturn(null); // default is false
        $repo->expects(self::once())->method('save');

        $service = $this->createService($repo);
        $result = $service->toggle($user, 'sidebar_collapsed');

        self::assertTrue($result);
    }

    public function testToggleFlipsBooleanFromTrueToFalse(): void
    {
        $user = $this->createUser();
        $pref = new UserPreference($user, 'sidebar_collapsed', '1');

        $repo = $this->createMock(UserPreferenceRepositoryInterface::class);
        $repo->method('findByUserAndField')->willReturn($pref);
        $repo->expects(self::once())->method('save');

        $service = $this->createService($repo);
        $result = $service->toggle($user, 'sidebar_collapsed');

        self::assertFalse($result);
    }

    public function testGetDefinitions(): void
    {
        $service = $this->createService();

        $defs = $service->getDefinitions();

        self::assertArrayHasKey('sidebar_collapsed', $defs);
        self::assertArrayHasKey('content_maximized', $defs);
        self::assertArrayHasKey('locale', $defs);
    }
}
