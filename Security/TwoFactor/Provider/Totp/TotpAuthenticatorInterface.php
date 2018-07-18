<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp;

use OTPHP\TOTP;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

interface TotpAuthenticatorInterface
{
    public function checkCode(TwoFactorInterface $user, string $code): bool;

    public function getUrl(TOTP $totp): string;

    public function generateNewTotp(): TOTP;

    public function getTotpForUser(TwoFactorInterface $user): TOTP;
}
