<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Csrf;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class NullCsrfTokenManager implements CsrfTokenManagerInterface
{
    /**
     * @param string $tokenId
     *
     * @return CsrfToken
     */
    public function getToken($tokenId)
    {
        return new CsrfToken($tokenId, '');
    }

    /**
     * @param string $tokenId
     *
     * @return CsrfToken
     */
    public function refreshToken($tokenId)
    {
        return new CsrfToken($tokenId, '');
    }

    /**
     * @param string $tokenId
     *
     * @return string|null
     */
    public function removeToken($tokenId)
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isTokenValid(CsrfToken $token)
    {
        return true;
    }
}
