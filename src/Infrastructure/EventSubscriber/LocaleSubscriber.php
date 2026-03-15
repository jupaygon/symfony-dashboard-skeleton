<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use App\Application\Service\UserPreferenceService;
use App\Domain\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserPreferenceService $preferenceService,
        private readonly Security $security,
        private readonly string $defaultLocale = 'en',
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        // Query param takes priority (from language selector)
        $locale = $request->query->get('_locale');

        if ($locale) {
            $request->setLocale($locale);
            $request->getSession()->set('_locale', $locale);

            // Save to user preference if logged in
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $this->preferenceService->set($user, 'locale', $locale);
            }

            return;
        }

        // Try user preference
        $user = $this->security->getUser();
        if ($user instanceof User) {
            $userLocale = $this->preferenceService->get($user, 'locale');
            if ($userLocale) {
                $request->setLocale($userLocale);
                $request->getSession()->set('_locale', $userLocale);

                return;
            }
        }

        // Fallback to session
        $sessionLocale = $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($sessionLocale);
    }
}
