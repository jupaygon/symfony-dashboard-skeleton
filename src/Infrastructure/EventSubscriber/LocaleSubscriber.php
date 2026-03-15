<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use App\Application\Service\UserPreferenceService;
use App\Domain\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPreferenceService $preferenceService,
        private Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                // After SessionListener (128), before LocaleListener (16)
                ['applyLocale', 100],
                // After SecurityFirewall (8): save to DB
                ['saveLocale', 4],
            ],
        ];
    }

    /**
     * Apply locale from query param or session.
     * Sets _locale as request attribute so Symfony's LocaleListener picks it up.
     */
    public function applyLocale(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        $queryLocale = $request->query->get('_locale');
        if ($queryLocale) {
            $request->attributes->set('_locale', $queryLocale);
            $request->setLocale($queryLocale);
            if ($request->hasSession()) {
                $request->getSession()->set('_locale', $queryLocale);
            }

            return;
        }

        if ($request->hasSession() && $request->getSession()->has('_locale')) {
            $sessionLocale = $request->getSession()->get('_locale');
            $request->attributes->set('_locale', $sessionLocale);
            $request->setLocale($sessionLocale);
        }
    }

    /**
     * Save locale to user preference in DB.
     * Runs after SecurityFirewall so user is available.
     */
    public function saveLocale(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !$request->hasSession()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        // Save to DB if locale came from query param
        $queryLocale = $request->query->get('_locale');
        if ($queryLocale) {
            $this->preferenceService->set($user, 'locale', $queryLocale);

            return;
        }

        // First visit after login: load from DB if no session locale
        if (!$request->getSession()->has('_locale')) {
            $userLocale = $this->preferenceService->get($user, 'locale');
            if ($userLocale) {
                $request->setLocale($userLocale);
                $request->getSession()->set('_locale', $userLocale);
            }
        }
    }
}
