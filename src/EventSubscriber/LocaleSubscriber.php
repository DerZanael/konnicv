<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Defines user locale in session
 * @FROM: https://symfony.com/doc/current/session/locale_sticky_session.html
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;
    
    public function __construct(string $defaultLocale = "en")
    {
        $this->defaultLocale = $defaultLocale;
    }
    
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if(!$request->hasPreviousSession()) {
            return;
        }

        //Set locale to session's value / otherwise use default app locale
        $request->setLocale($request->getSession()->get("_locale", $this->defaultLocale));
    }

    public static function getSubscribedEvents()
    {
        return [
            // 'kernel.request' => 'onKernelRequest',
            KernelEvents::REQUEST => [["onKernelRequest", 20]],
        ];
    }
}
