<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\CsrfProtection;

class CsrfProtectionConfiguration
{
    private $csrfProtectionEnabled;
    private $csrfFieldName;
    private $csrfTokenId;

    public function __construct(
        bool $csrfProtectionEnabled,
        string $csrfFieldName,
        string $csrfTokenId
    ) {
        $this->csrfProtectionEnabled = $csrfProtectionEnabled;
        $this->csrfFieldName = $csrfFieldName;
        $this->csrfTokenId = $csrfTokenId;
    }

    public function isCsrfProtectionEnabled()
    {
        return $this->csrfProtectionEnabled;
    }

    public function getCsrfFieldName()
    {
        return $this->csrfFieldName;
    }

    public function getCsrfTokenId()
    {
        return $this->csrfTokenId;
    }
}
