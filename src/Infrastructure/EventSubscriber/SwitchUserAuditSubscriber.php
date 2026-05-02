<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use App\Domain\Model\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Records every `_switch_user` impersonation event so admin actions executed
 * while impersonating a target user remain traceable.
 *
 * Logs an INFO entry with: actor email, target email, action (impersonate /
 * exit), client IP, and timestamp.
 */
final readonly class SwitchUserAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $token = $event->getToken();
        $target = $event->getTargetUser();

        $actorEmail = 'unknown';
        $action = 'impersonate';

        if ($token instanceof SwitchUserToken) {
            // Exiting impersonation: the original (admin) token is the parent
            $original = $token->getOriginalToken();
            $originalUser = $original->getUser();
            if ($originalUser instanceof User) {
                $actorEmail = $originalUser->getUserIdentifier();
            }
            $action = 'exit';
        } else {
            $user = $token->getUser();
            if ($user instanceof User) {
                $actorEmail = $user->getUserIdentifier();
            }
        }

        $targetEmail = $target instanceof User
            ? $target->getUserIdentifier()
            : (string) $target->getUserIdentifier();

        $this->logger->info('switch_user', [
            'actor'  => $actorEmail,
            'target' => $targetEmail,
            'action' => $action,
            'ip'     => $this->requestStack->getCurrentRequest()?->getClientIp(),
        ]);
    }
}
