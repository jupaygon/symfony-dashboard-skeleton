<?php

namespace App\Infrastructure\EventSubscriber;

use App\Infrastructure\Service\Log;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class LoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        Log::setLogger($this->logger);
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        Log::setLogger($this->logger);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            ConsoleEvents::COMMAND => 'onConsoleCommand',
        ];
    }
}
