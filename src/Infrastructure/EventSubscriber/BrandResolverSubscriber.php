<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use App\Application\Service\BrandContext;
use App\Application\Service\UserPreferenceService;
use App\Domain\Model\User;
use App\Infrastructure\Branding\BrandResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class BrandResolverSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BrandResolver $brandResolver,
        private BrandContext $brandContext,
        private Security $security,
        private UserPreferenceService $preferenceService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['resolveByHost', 100],
                ['applyUserOverride', 4],
            ],
        ];
    }

    public function resolveByHost(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $host = $event->getRequest()->getHost();
        $brand = $this->brandResolver->resolve($host);
        $this->brandContext->set($brand);

        $event->getRequest()->headers->set('Vary', 'Host');
    }

    public function applyUserOverride(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || !$user->isSuperAdmin()) {
            return;
        }

        $override = $this->preferenceService->get($user, 'brand_override');
        if ($override) {
            $brand = $this->brandResolver->resolveByKey($override);
            if ($brand) {
                $this->brandContext->set($brand);
            }
        }
    }
}
