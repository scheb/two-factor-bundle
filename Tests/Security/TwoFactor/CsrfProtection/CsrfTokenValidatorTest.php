<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\CsrfProtection;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\CsrfProtection\CsrfTokenValidator;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfTokenValidatorTest extends TestCase
{
    private const FIREWALL_NAME = 'firewallName';
    private const CSRF_TOKEN_GENERATOR = null;
    private const CSRF_PARAMETER_NAME = 'parameter_name';
    private const CSRF_TOKEN_ID = 'token_id';

    /**
     * @var MockObject|ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var MockObject|CsrfTokenManagerInterface
     */
    private $csrfTokenGenerator;

    /**
     * @var CsrfTokenValidator
     */
    private $csrfTokenValidator;

    protected function setUp()
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->parameterBag
            ->expects($this->any())
            ->method('get')
            ->willReturn('token_value');

        $this->request = $this->createMock(Request::class);
        $this->request->request = $this->parameterBag;

        $this->csrfTokenGenerator = $this->createMock(CsrfTokenManagerInterface::class);

        $options = [
            'csrf_token_generator' => self::CSRF_TOKEN_GENERATOR,
            'csrf_parameter_name' => self::CSRF_PARAMETER_NAME,
            'csrf_token_id' => self::CSRF_TOKEN_ID,
        ];

        $this->csrfTokenValidator = new CsrfTokenValidator(self::FIREWALL_NAME, $options);
    }

    private function createCsrfTokenValidator(array $options): CsrfTokenValidator
    {
        return new CsrfTokenValidator(self::FIREWALL_NAME, $options);
    }

    private function stubTokenIsInvalid(): void
    {
        $this->csrfTokenGenerator
            ->expects($this->any())
            ->method('isTokenValid')
            ->willReturn(false);
    }

    private function stubTokenIsValid(): void
    {
        $this->csrfTokenGenerator
            ->expects($this->any())
            ->method('isTokenValid')
            ->willReturn(true);
    }

    /**
     * @test
     */
    public function supports_firewallNameNotTheSame_returnFalse()
    {
        $this->assertFalse($this->csrfTokenValidator->supports('fooBar'));
    }

    /**
     * @test
     */
    public function supports_firewallNameTheSame_returnTrue()
    {
        $this->assertTrue($this->csrfTokenValidator->supports(self::FIREWALL_NAME));
    }

    /**
     * @test
     */
    public function hasValidCsrfToken_csrfTokenGeneratorIsNull_returnFalse()
    {
        $this->assertFalse($this->csrfTokenValidator->hasValidCsrfToken($this->request));
    }

    /**
     * @test
     */
    public function hasValidCsrfToken_tokenIsInvalid_returnFalse()
    {
        $this->stubTokenIsInvalid();

        $csrfTokenValidator = $this->createCsrfTokenValidator([
            'csrf_token_generator' => $this->csrfTokenGenerator,
            'csrf_parameter_name' => self::CSRF_PARAMETER_NAME,
            'csrf_token_id' => self::CSRF_TOKEN_ID,
        ]);

        $this->assertFalse($csrfTokenValidator->hasValidCsrfToken($this->request));
    }

    /**
     * @test
     */
    public function hasValidCsrfToken_tokenIsValid_returnTrue()
    {
        $this->stubTokenIsValid();

        $csrfTokenValidator = $this->createCsrfTokenValidator([
            'csrf_token_generator' => $this->csrfTokenGenerator,
            'csrf_parameter_name' => self::CSRF_PARAMETER_NAME,
            'csrf_token_id' => self::CSRF_TOKEN_ID,
        ]);

        $this->assertTrue($csrfTokenValidator->hasValidCsrfToken($this->request));
    }
}
