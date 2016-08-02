<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Event;

use Symfony\Component\EventDispatcher\Event;

class  TwoFactorAuthFailureEvent extends Event
{
    const NAME = 'scheb_two_factor.authentication.failure';
}
