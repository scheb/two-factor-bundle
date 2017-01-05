<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Yubikey;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Yubikey\TwoFactorProvider;
use Symfony\Component\HttpFoundation\Response;
use Scheb\TwoFactorBundle\Tests\TestCase;

class TwoFactorProviderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $yubikeyService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $renderer;

    /**
     * @var TwoFactorProvider
     */
    private $provider;

    public function setUp()
    {
        $this->yubikeyService = $this->createMock('Surfnet\YubikeyApiClientBundle\Service\VerificationService');
        $this->renderer = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\Renderer');
        $this->provider = new TwoFactorProvider($this->authenticator, $this->renderer, 'authCodeName');
    }




}
