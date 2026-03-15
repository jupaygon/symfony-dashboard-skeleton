<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use App\Application\Service\BrandContext;
use App\Infrastructure\Branding\BrandResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class BrandResolverSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BrandResolver $brandResolver,
        private BrandContext $brandContext,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $host = $event->getRequest()->getHost();
        $brand = $this->brandResolver->resolve($host);
        $this->brandContext->set($brand);

        $event->getRequest()->headers->set('Vary', 'Host');
    }
}
