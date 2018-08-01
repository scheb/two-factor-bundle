<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\CsrfProtection\CsrfProtectionConfiguration;
use Scheb\TwoFactorBundle\Tests\TestCase;

class CsrfProtectionConfigurationTest extends TestCase
{
    /**
     * @var CsrfProtectionConfiguration
     */
    private $config;

    /**
     * @var bool
     */
    private $csrfProtectionEnabled = true;

    /**
     * @var string
     */
    private $csrfFieldName = '_token';

    /**
     * @var string
     */
    private $csrfTokenId = 'two_factor_csrf_token';

    protected function setUp()
    {
        $this->config = new CsrfProtectionConfiguration(
            $this->csrfProtectionEnabled,
            $this->csrfFieldName,
            $this->csrfTokenId
        );
    }

    /**
     * @test
     */
    public function isCsrfProtectionEnabled()
    {
        $this->assertSame($this->csrfProtectionEnabled, $this->config->isCsrfProtectionEnabled());
    }

    /**
     * @test
     */
    public function getCsrfFieldName()
    {
        $this->assertSame($this->csrfFieldName, $this->config->getCsrfFieldName());
    }

    /**
     * @test
     */
    public function getCsrfTokenId()
    {
        $this->assertSame($this->csrfTokenId, $this->config->getCsrfTokenId());
    }
}
