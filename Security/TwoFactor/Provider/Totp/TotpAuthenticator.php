<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp;

use OTPHP\Factory;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

class TotpAuthenticator implements TotpAuthenticatorInterface
{
    private $issuer;
    private $period;
    private $digits;
    private $digest;
    private $customParameters;
    private $qrCodeGenerator;
    private $qrCodeDataPlaceholder;

    public function __construct(?string $issuer, int $period, int $digits, string $digest, array $customParameters, string $qrCodeGenerator, string $qrCodeDataPlaceholder)
    {
        $this->issuer = $issuer;
        $this->digits = $digits;
        $this->digest = $digest;
        $this->customParameters = $customParameters;
        $this->period = $period;
        $this->qrCodeGenerator = $qrCodeGenerator;
        $this->qrCodeDataPlaceholder = $qrCodeDataPlaceholder;
    }

    public function checkCode(TwoFactorInterface $user, string $code): bool
    {
        $totp = $this->getTotpForUser($user);

        return $totp->verify($code);
    }

    public function getUrl(TOTP $totp): string
    {
        return $totp->getQrCodeUri($this->qrCodeGenerator, $this->qrCodeDataPlaceholder);
    }

    public function generateNewTotp(): TOTP
    {
        $totp = TOTP::create(
            trim(Base32::encodeUpper(random_bytes(32)), '='),
            $this->period,
            $this->digest,
            $this->digits
        );
        if ($this->issuer) {
            $totp->setIssuer($this->issuer);
        }
        foreach ($this->customParameters as $key => $value) {
            $totp->setParameter($key, $value);
        }

        return $totp;
    }

    public function getTotpForUser(TwoFactorInterface $user): TOTP
    {
        return Factory::loadFromProvisioningUri($user->getTotpAuthenticationProvisioningUri());
    }
}
