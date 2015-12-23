<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

class TrustedTokenGenerator
{
    /**
     * @var string
     */
    protected $charspace = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Generate trusted computer token.
     *
     * @param int $length
     *
     * @return string
     */
    public function generateToken($length)
    {
        return substr(base64_encode(random_bytes($length)), 0, $length);
    }
}
