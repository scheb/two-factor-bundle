<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp;

use OTPHP\Factory;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

interface TotpFactoryInterface
{
    /**
     * @return TOTP
     */
    public function generateNewTotp(): TOTP;

    /**
     * @param TwoFactorInterface $user
     *
     * @return TOTP
     */
    public function getTotpForUser(TwoFactorInterface $user): TOTP;
}
