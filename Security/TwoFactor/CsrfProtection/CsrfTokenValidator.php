<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\CsrfProtection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfTokenValidator
{
    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var CsrfProtectionConfiguration
     */
    private $csrfProtectionConfiguration;

    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        CsrfProtectionConfiguration $csrfProtectionConfiguration
    ) {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->csrfProtectionConfiguration = $csrfProtectionConfiguration;
    }

    public function validate(Request $request): void
    {
        $tokenValue = $request->request->get($this->csrfProtectionConfiguration->getCsrfFieldName(), '');
        $token = new CsrfToken($this->csrfProtectionConfiguration->getCsrfTokenId(), $tokenValue);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
    }
}
