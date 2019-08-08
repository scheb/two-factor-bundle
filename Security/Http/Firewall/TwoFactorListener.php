<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\Security\Http\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

// Symfony < 4.3
if (!class_exists(RequestEvent::class)) {
    class TwoFactorListener implements ListenerInterface
    {
        use TwoFactorListenerTrait;

        public function handle(GetResponseEvent $event)
        {
            $this->doHandleOrInvoke($event);
        }
    }
} else {
    class TwoFactorListener
    {
        use TwoFactorListenerTrait;

        public function __invoke(RequestEvent $event)
        {
            $this->doHandleOrInvoke($event);
        }
    }
}
