<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

interface GoogleAuthenticatorInterface
{
    /**
     * Validates the code, which was entered by the user.
     */
    public function checkCode(TwoFactorInterface $user, string $code): bool;

    /**
     * Generate the URL of a QR code, which can be scanned by Google Authenticator app.
     */
    public function getUrl(TwoFactorInterface $user): string;

    /**
     * Generate the content for a QR-Code to be scanned by Google Authenticator
     * Use this method if you don't want to use google charts to display the qr-code.
     */
    public function getQRContent(TwoFactorInterface $user): string;

    /**
     * Generate a new secret for Google Authenticator.
     */
    public function generateSecret(): string;
}
