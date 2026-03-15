<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Application\Service\UserPreferenceService;
use App\Domain\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserPreferenceExtension extends AbstractExtension
{
    public function __construct(
        private readonly UserPreferenceService $preferenceService,
        private readonly Security $security,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_preference', [$this, 'getUserPreference']),
        ];
    }

    public function getUserPreference(string $field): mixed
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return $this->preferenceService->getDefinitions()[$field]['default'] ?? null;
        }

        return $this->preferenceService->get($user, $field);
    }
}
