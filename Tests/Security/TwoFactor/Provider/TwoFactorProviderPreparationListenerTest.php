<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderPreparationListener;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderPreparationRecorder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class TwoFactorProviderPreparationListenerTest extends TestCase
{
    private const FIREWALL_NAME = 'firewallName';
    private const CURRENT_PROVIDER_NAME = 'currentProviderName';

    /**
     * @var MockObject|TwoFactorProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var MockObject|TwoFactorProviderPreparationRecorder
     */
    private $preparationRecorder;

    /**
     * @var MockObject|TwoFactorToken
     */
    private $twoFactorToken;

    /**
     * @var \stdClass
     */
    private $user;

    /**
     * @var TwoFactorProviderPreparationListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->user = new \stdClass();
        $this->twoFactorToken = $this->createMock(TwoFactorToken::class);
        $this->twoFactorToken
            ->expects($this->any())
            ->method('getProviderKey')
            ->willReturn(self::FIREWALL_NAME);
        $this->twoFactorToken
            ->expects($this->any())
            ->method('getCurrentTwoFactorProvider')
            ->willReturn(self::CURRENT_PROVIDER_NAME);
        $this->twoFactorToken
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($this->user);

        $this->preparationRecorder = $this->createMock(TwoFactorProviderPreparationRecorder::class);

        $this->providerRegistry = $this->createMock(TwoFactorProviderRegistry::class);
    }

    private function initTwoFactorProviderPreparationListener($prepareOnLogin, $prepareOnAccessDenied): void
    {
        $this->listener = new TwoFactorProviderPreparationListener(
            $this->providerRegistry,
            $this->preparationRecorder,
            $this->createMock(LoggerInterface::class),
            self::FIREWALL_NAME,
            $prepareOnLogin,
            $prepareOnAccessDenied
        );
    }

    private function createTwoFactorAuthenticationEvent(): TwoFactorAuthenticationEvent
    {
        return new TwoFactorAuthenticationEvent($this->request, $this->twoFactorToken);
    }

    private function createAuthenticationEvent(TokenInterface $token): AuthenticationEvent
    {
        return new AuthenticationEvent($token);
    }

    private function createFinishRequestEvent(): FinishRequestEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        // Class is final, have to use a real instance instead of a mock
        return new FinishRequestEvent($kernel, $this->request, HttpKernelInterface::MASTER_REQUEST);
    }

    private function expectPrepareCurrentProvider(): void
    {
        $twoFactorProvider = $this->createMock(TwoFactorProviderInterface::class);
        $this->preparationRecorder
            ->expects($this->once())
            ->method('isProviderPrepared')
            ->with(self::FIREWALL_NAME, self::CURRENT_PROVIDER_NAME)
            ->willReturn(false);

        $this->providerRegistry
            ->expects($this->once())
            ->method('getProvider')
            ->with(self::CURRENT_PROVIDER_NAME)
            ->willReturn($twoFactorProvider);

        $this->preparationRecorder
            ->expects($this->once())
            ->method('recordProviderIsPrepared')
            ->with(self::FIREWALL_NAME, self::CURRENT_PROVIDER_NAME);

        $twoFactorProvider
            ->expects($this->once())
            ->method('prepareAuthentication')
            ->with($this->identicalTo($this->user));

        $this->preparationRecorder
            ->expects($this->once())
            ->method('saveSession');
    }

    private function expectNotPrepareCurrentProvider(): void
    {
        $this->preparationRecorder
            ->expects($this->never())
            ->method('recordProviderIsPrepared');

        $this->providerRegistry
            ->expects($this->never())
            ->method('getProvider');
    }

    /**
     * @test
     */
    public function onLogin_optionPrepareOnLoginTrue_twoFactorProviderIsPrepared(): void
    {
        $this->initTwoFactorProviderPreparationListener(true, false);
        $event = $this->createAuthenticationEvent($this->twoFactorToken);

        $this->expectPrepareCurrentProvider();

        $this->listener->onLogin($event);
        $this->listener->onKernelFinishRequest($this->createFinishRequestEvent());
    }

    /**
     * @test
     */
    public function onLogin_optionPrepareOnLoginFalse_twoFactorProviderIsNotPrepared(): void
    {
        $this->initTwoFactorProviderPreparationListener(false, false);
        $event = $this->createAuthenticationEvent($this->twoFactorToken);

        $this->expectNotPrepareCurrentProvider();

        $this->listener->onLogin($event);
        $this->listener->onKernelFinishRequest($this->createFinishRequestEvent());
    }

    /**
     * @test
     */
    public function onLogin_twoFactorToken_startRecording(): void
    {
        $this->initTwoFactorProviderPreparationListener(false, false);
        $event = $this->createAuthenticationEvent($this->twoFactorToken);

        $this->preparationRecorder
            ->expects($this->once())
            ->method('startRecording')
            ->with(self::FIREWALL_NAME);

        $this->listener->onLogin($event);
    }

    /**
     * @test
     */
    public function onLogin_otherToken_doNothing(): void
    {
        $this->initTwoFactorProviderPreparationListener(false, false);
        $token = $this->createMock(TokenInterface::class);
        $event = $this->createAuthenticationEvent($token);

        $this->preparationRecorder
            ->expects($this->never())
            ->method('startRecording');

        $this->listener->onLogin($event);
    }

    /**
     * @test
     */
    public function onAccessDenied_optionPrepareOnAccessDeniedTrue_twoFactorProviderIsPrepared(): void
    {
        $this->initTwoFactorProviderPreparationListener(false, true);
        $event = $this->createTwoFactorAuthenticationEvent();

        $this->expectPrepareCurrentProvider();

        $this->listener->onAccessDenied($event);
        $this->listener->onKernelFinishRequest($this->createFinishRequestEvent());
    }

    /**
     * @test
     */
    public function onAccessDenied_optionPrepareOnAccessDeniedFalse_twoFactorProviderIsNotPrepared(): void
    {
        $this->initTwoFactorProviderPreparationListener(false, false);
        $event = $this->createTwoFactorAuthenticationEvent();

        $this->expectNotPrepareCurrentProvider();

        $this->listener->onAccessDenied($event);
        $this->listener->onKernelFinishRequest($this->createFinishRequestEvent());
    }

    /**
     * @test
     */
    public function onTwoFactorForm_onEvent_twoFactorProviderIsPrepared(): void
    {
        $this->initTwoFactorProviderPreparationListener(false, false);
        $event = $this->createTwoFactorAuthenticationEvent();

        $this->expectPrepareCurrentProvider();

        $this->listener->onTwoFactorForm($event);
        $this->listener->onKernelFinishRequest($this->createFinishRequestEvent());
    }

    /**
     * @test
     */
    public function onKernelFinishRequest_providerAlreadyPrepared_saveSession(): void
    {
        $this->initTwoFactorProviderPreparationListener(true, true);
        $event = $this->createTwoFactorAuthenticationEvent();

        $this->preparationRecorder
            ->expects($this->once())
            ->method('isProviderPrepared')
            ->with(self::FIREWALL_NAME, self::CURRENT_PROVIDER_NAME)
            ->willReturn(true);

        $this->preparationRecorder
            ->expects($this->once())
            ->method('saveSession');

        $this->preparationRecorder
            ->expects($this->never())
            ->method('recordProviderIsPrepared');

        $this->providerRegistry
            ->expects($this->never())
            ->method('getProvider');

        $this->listener->onTwoFactorForm($event);
        $this->listener->onKernelFinishRequest($this->createFinishRequestEvent());
    }
}
