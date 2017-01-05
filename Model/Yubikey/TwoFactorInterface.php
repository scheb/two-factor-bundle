<?php

namespace Scheb\TwoFactorBundle\Model\Yubikey;

interface TwoFactorInterface
{

    /**
     * Return the Yubikey unique ID
     * When an empty string or null is returned, the yubikey authentication is disabled.
     *
     * @return string|null
     */
    public function getYubikeyId();

    /**
     * Set the yubikey ID.
     *
     * @param int $googleAuthenticatorSecret
     */
    public function setYubikeyId($yubikeyId);
}
