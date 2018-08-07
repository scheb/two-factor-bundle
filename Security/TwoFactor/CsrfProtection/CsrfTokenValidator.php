<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\CsrfProtection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfTokenValidator
{
    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var array
     */
    private $options;

    public function __construct(string $firewallName, array $options)
    {
        $this->firewallName = $firewallName;
        $this->options = $options;
    }

    public function supports(string $firewallName): bool
    {
        return $this->firewallName === $firewallName;
    }

    public function hasValidCsrfToken(Request $request): bool
    {
        $csrfTokenGenerator = $this->options['csrf_token_generator'];
        if (!$csrfTokenGenerator instanceof CsrfTokenManagerInterface) {
            // Should not be called without a configured CSRF token generator.
            return false;
        }

        $tokenValue = $request->request->get($this->options['csrf_parameter_name'], '');
        $token = new CsrfToken($this->options['csrf_token_id'], $tokenValue);
        if (!$csrfTokenGenerator->isTokenValid($token)) {
            return false;
        }

        return true;
    }
}
