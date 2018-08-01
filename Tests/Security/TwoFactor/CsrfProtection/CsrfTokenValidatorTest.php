<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\CsrfProtection;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\CsrfProtection\CsrfProtectionConfiguration;
use Scheb\TwoFactorBundle\Security\TwoFactor\CsrfProtection\CsrfTokenValidator;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfTokenValidatorTest extends TestCase
{
    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var MockObject|CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var MockObject|CsrfProtectionConfiguration
     */
    private $csrfProtectionConfiguration;

    /**
     * @var string
     */
    private $fieldName = 'field_name';

    /**
     * @var MockObject|ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var CsrfTokenValidator
     */
    private $csrfTokenValidator;

    protected function setUp()
    {
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->csrfProtectionConfiguration = $this->createMock(CsrfProtectionConfiguration::class);

        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->parameterBag
            ->expects($this->any())
            ->method('get')
            ->willReturn('token_value');

        $this->request = $this->createMock(Request::class);
        $this->request->request = $this->parameterBag;

        $this->csrfProtectionConfiguration
            ->expects($this->any())
            ->method('getCsrfFieldName')
            ->willReturn($this->fieldName);
        $this->csrfProtectionConfiguration
            ->expects($this->any())
            ->method('getCsrfTokenId')
            ->willReturn('token_id');

        $this->csrfTokenValidator = new CsrfTokenValidator($this->csrfTokenManager, $this->csrfProtectionConfiguration);
    }

    /**
     * @test
     */
    public function validate_tokenIsValid()
    {
        $this->csrfTokenManager
            ->expects($this->any())
            ->method('isTokenValid')
            ->willReturn(true);

        $this->csrfTokenValidator->validate($this->request);
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function validate_tokenIsInvalid()
    {
        $this->csrfTokenManager
            ->expects($this->any())
            ->method('isTokenValid')
            ->willReturn(false);

        $this->expectException(InvalidCsrfTokenException::class);
        $this->csrfTokenValidator->validate($this->request);
    }
}
