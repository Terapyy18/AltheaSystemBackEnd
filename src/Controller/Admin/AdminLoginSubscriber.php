<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class AdminLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private RequestStack $requestStack
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        if ($event->getFirewallName() !== 'admin') {
            return;
        }

        $user = $event->getUser();

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->requestStack->getSession()->getFlashBag()->add(
                'error',
                'Vous n\'avez pas accès à cette page. Réservé aux administrateurs.'
            );

            $event->setResponse(
                new RedirectResponse($this->router->generate('admin_login'))
            );
        }
    }
}