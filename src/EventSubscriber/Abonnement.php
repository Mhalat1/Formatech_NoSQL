<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Abonnement implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['handleException', 600], // donne priorité haute 
        ];
    }

    public function handleException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        

        if ($exception instanceof AccessDeniedException) {
            $event->stopPropagation(); // Empêche les autres listeners de traiter cette exception
            $event->setResponse(
                new RedirectResponse($this->urlGenerator->generate('liste_abonnements'))
            );
        }
    }
}