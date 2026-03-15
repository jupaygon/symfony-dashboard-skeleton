<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Application\Service\BrandContext;
use App\Application\Service\UserPreferenceService;
use App\Domain\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserPreferenceExtension extends AbstractExtension
{
    /** @param array<string, array{name: string, menu?: string}> $brandDefs */
    public function __construct(
        private readonly UserPreferenceService $preferenceService,
        private readonly Security $security,
        private readonly BrandContext $brandContext,
        #[Autowire('%brands_defs%')] private readonly array $brandDefsConfig,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_preference', [$this, 'getUserPreference']),
            new TwigFunction('brand_defs', [$this, 'getBrandDefs']),
            new TwigFunction('current_brand', [$this, 'getCurrentBrand']),
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

    /** @return array<string, array{name: string, menu?: string}> */
    public function getBrandDefs(): array
    {
        return $this->brandDefsConfig;
    }

    public function getCurrentBrand(): \App\Domain\ValueObject\Brand
    {
        return $this->brandContext->get();
    }
}
