<?php

namespace Scheb\TwoFactorBundle\Tests\Security\Http\Firewall;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenFactory;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenFactoryInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Voter\TwoFactorInProgressVoter;
use Scheb\TwoFactorBundle\Security\Http\Authentication\AuthenticationRequiredHandlerInterface;
use Scheb\TwoFactorBundle\Security\Http\Firewall\TwoFactorListener;
use Scheb\TwoFactorBundle\Security\TwoFactor\Csrf\CsrfTokenValidator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManagerInterface;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class TwoFactorListenerTest extends TestCase
{
    private const FORM_PATH = '/form_path';
    private const CHECK_PATH = '/check_path';
    private const AUTH_CODE_PARAM = 'auth_code_param';
    private const TRUSTED_PARAM = 'trusted_param';
    private const FIREWALL_NAME = 'firewallName';

    /**
     * @var MockObject|TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var MockObject|AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var MockObject|HttpUtils
     */
    private $httpUtils;

    /**
     * @var MockObject|AuthenticationSuccessHandlerInterface
     */
    private $successHandler;

    /**
     * @var MockObject|AuthenticationFailureHandlerInterface
     */
    private $failureHandler;

    /**
     * @var MockObject|AuthenticationRequiredHandlerInterface
     */
    private $authenticationRequiredHandler;

    /**
     * @var MockObject|CsrfTokenValidator
     */
    private $csrfTokenValidator;

    /**
     * @var MockObject|TrustedDeviceManagerInterface
     */
    private $trustedDeviceManager;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MockObject|TwoFactorTokenFactoryInterface
     */
    private $twoFactorTokenFactory;

    /**
     * @var MockObject|AccessMapInterface
     */
    private $accessMap;

    /**
     * @var MockObject|AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    /**
     * @var MockObject|GetResponseEvent
     */
    private $getResponseEvent;

    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var array
     */
    private $requestParams = [
        self::AUTH_CODE_PARAM => 'authCode',
        self::TRUSTED_PARAM => null,
    ];

    /**
     * @var MockObject|RedirectResponse
     */
    private $authFormRedirectResponse;

    /**
     * @var TwoFactorListener
     */
    private $listener;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->httpUtils = $this->createMock(HttpUtils::class);
        $this->successHandler = $this->createMock(AuthenticationSuccessHandlerInterface::class);
        $this->failureHandler = $this->createMock(AuthenticationFailureHandlerInterface::class);
        $this->authenticationRequiredHandler = $this->createMock(AuthenticationRequiredHandlerInterface::class);
        $this->csrfTokenValidator = $this->createMock(CsrfTokenValidator::class);
        $this->trustedDeviceManager = $this->createMock(TrustedDeviceManagerInterface::class);
        $this->accessMap = $this->createMock(AccessMapInterface::class);
        $this->accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->twoFactorTokenFactory = $this->createMock(TwoFactorTokenFactory::class);

        $this->request = $this->createMock(Request::class);
        $this->request
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function (string $param) {
                return $this->requestParams[$param];
            });

        $this->getResponseEvent = $this->createMock(GetResponseEvent::class);
        $this->getResponseEvent
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->authFormRedirectResponse = $this->createMock(RedirectResponse::class);

        $options = [
            'auth_form_path' => self::FORM_PATH,
            'check_path' => self::CHECK_PATH,
            'auth_code_parameter_name' => self::AUTH_CODE_PARAM,
            'trusted_parameter_name' => self::TRUSTED_PARAM,
        ];

        $this->listener = new TwoFactorListener(
            $this->tokenStorage,
            $this->authenticationManager,
            $this->httpUtils,
            self::FIREWALL_NAME,
            $this->successHandler,
            $this->failureHandler,
            $this->authenticationRequiredHandler,
            $this->csrfTokenValidator,
            $options,
            $this->trustedDeviceManager,
            $this->accessMap,
            $this->accessDecisionManager,
            $this->eventDispatcher,
            $this->twoFactorTokenFactory,
            $this->createMock(LoggerInterface::class)
        );
    }

    private function createTwoFactorToken($firewallName = self::FIREWALL_NAME): MockObject
    {
        $twoFactorToken = $this->createMock(TwoFactorToken::class);
        $twoFactorToken
            ->expects($this->any())
            ->method('getProviderKey')
            ->willReturn($firewallName);

        $this->twoFactorTokenFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($twoFactorToken);

        return $twoFactorToken;
    }

    private function stubTokenManagerHasToken(TokenInterface $token): void
    {
        $this->tokenStorage
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);
    }

    private function stubCurrentPath(string $currentPath): void
    {
        $this->request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn($currentPath);

        $this->httpUtils
            ->expects($this->any())
            ->method('checkRequestPath')
            ->with($this->request)
            ->willReturnCallback(function ($request, $pathToCheck) use ($currentPath) {
                return $currentPath === $pathToCheck;
            });
    }

    private function stubRequestHasParameter(string $parameterName, $value): void
    {
        $this->requestParams[$parameterName] = $value;
    }

    private function stubHandlersReturnResponse(): void
    {
        $this->successHandler
            ->expects($this->any())
            ->method('onAuthenticationSuccess')
            ->willReturn($this->createMock(Response::class));
        $this->failureHandler
            ->expects($this->any())
            ->method('onAuthenticationFailure')
            ->willReturn($this->createMock(Response::class));
    }

    private function stubAuthenticationManagerReturnsToken(MockObject $returnedToken): void
    {
        $this->authenticationManager
            ->expects($this->any())
            ->method('authenticate')
            ->willReturn($returnedToken);
    }

    private function stubAuthenticationManagerThrowsAuthenticationException(): void
    {
        $this->authenticationManager
            ->expects($this->any())
            ->method('authenticate')
            ->willThrowException(new AuthenticationException());
    }

    private function stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue(): void
    {
        $this->csrfTokenValidator
            ->expects($this->any())
            ->method('hasValidCsrfToken')
            ->willReturn(true);
    }

    private function stubCsrfTokenValidatorHasValidCsrfTokenReturnsFalse(): void
    {
        $this->csrfTokenValidator
            ->expects($this->any())
            ->method('hasValidCsrfToken')
            ->willReturn(false);
    }

    private function stubPathAccessGranted(bool $accessGranted): void
    {
        $this->accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->willReturn([[TwoFactorInProgressVoter::IS_AUTHENTICATED_2FA_IN_PROGRESS], 'https']);
        $this->accessDecisionManager
            ->expects($this->any())
            ->method('decide')
            ->with($this->isInstanceOf(TwoFactorToken::class), [TwoFactorInProgressVoter::IS_AUTHENTICATED_2FA_IN_PROGRESS], $this->request)
            ->willReturn($accessGranted);
    }

    private function assertPathNotChecked(): void
    {
        $this->httpUtils
            ->expects($this->never())
            ->method($this->anything());
    }

    private function assertNoResponseSet(): void
    {
        $this->getResponseEvent
            ->expects($this->never())
            ->method('getResponse');
    }

    private function assertRedirectToAuthForm(): void
    {
        $this->getResponseEvent
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->identicalTo($this->authFormRedirectResponse));
    }

    private function assertEventsDispatched(array $eventTypes): void
    {
        $numEvents = count($eventTypes);
        $consecutiveParams = [];
        foreach ($eventTypes as $eventType) {
            $consecutiveParams[] = [$eventType, $this->isInstanceOf(TwoFactorAuthenticationEvent::class)];
        }
        $this->eventDispatcher
            ->expects($this->exactly($numEvents))
            ->method('dispatch')
            ->withConsecutive(...$consecutiveParams);
    }

    /**
     * @test
     */
    public function handle_noTwoFactorToken_doNothing()
    {
        $this->stubTokenManagerHasToken($this->createMock(TokenInterface::class));

        $this->assertPathNotChecked();
        $this->assertNoResponseSet();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_differentFirewallName_doNothing()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken('otherFirewallName'));

        $this->assertPathNotChecked();
        $this->assertNoResponseSet();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_neitherFormNorCheckPath_redirectToForm()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath('/some_other_path');
        $this->stubPathAccessGranted(false);

        $this->authenticationRequiredHandler
            ->expects($this->once())
            ->method('onAuthenticationRequired')
            ->willReturn($this->authFormRedirectResponse);

        $this->assertRedirectToAuthForm();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_neitherFormNorCheckPath_dispatchRequireEvent()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath('/some_other_path');
        $this->stubPathAccessGranted(false);

        $this->assertEventsDispatched([
            TwoFactorAuthenticationEvents::REQUIRE,
        ]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_pathAccessibleDuringTwoFactorAuthentication_notRedirectToForm()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath('/some_other_path');
        $this->stubPathAccessGranted(true);

        $this->assertNoResponseSet();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_isAuthFormPath_doNothing()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::FORM_PATH);

        $this->assertNoResponseSet();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_isCheckPath_authenticateWithAuthenticationManager()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubHandlersReturnResponse();

        $tokenAssert = function ($token): bool {
            /* @var TwoFactorToken $token */
            $this->assertInstanceOf(TwoFactorToken::class, $token);

            return true;
        };

        $method = new \ReflectionMethod(TwoFactorListener::class, 'getAuthCodeFromRequest');
        $method->setAccessible(true);
        $this->assertEquals('authCode', $method->invokeArgs($this->listener, [$this->request]));

        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->callback($tokenAssert))
            ->willReturn($this->createMock(TokenInterface::class));

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationException_dispatchFailureEvent()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubAuthenticationManagerThrowsAuthenticationException();
        $this->stubHandlersReturnResponse();

        $this->assertEventsDispatched([
            TwoFactorAuthenticationEvents::ATTEMPT,
            TwoFactorAuthenticationEvents::FAILURE,
        ]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationException_setResponseFromFailureHandler()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubAuthenticationManagerThrowsAuthenticationException();

        $response = $this->createMock(Response::class);
        $this->failureHandler
            ->expects($this->once())
            ->method('onAuthenticationFailure')
            ->willReturn($response);

        $this->getResponseEvent
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->identicalTo($response));

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_csrfTokenInvalid_dispatchFailureEvent()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsFalse();
        $this->stubHandlersReturnResponse();

        $this->assertEventsDispatched([TwoFactorAuthenticationEvents::FAILURE]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationStepSuccessful_dispatchSuccessEvent()
    {
        $twoFactorToken = $this->createTwoFactorToken();
        $this->stubTokenManagerHasToken($twoFactorToken);
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubAuthenticationManagerReturnsToken($twoFactorToken); // Must be TwoFactorToken

        $this->assertEventsDispatched([
            TwoFactorAuthenticationEvents::ATTEMPT,
            TwoFactorAuthenticationEvents::SUCCESS,
            $this->anything(),
        ]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationStepSuccessfulButNotCompleted_redirectToAuthenticationForm()
    {
        $twoFactorToken = $this->createTwoFactorToken();
        $this->stubTokenManagerHasToken($twoFactorToken);
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubAuthenticationManagerReturnsToken($twoFactorToken); // Must be TwoFactorToken

        $this->authenticationRequiredHandler
            ->expects($this->once())
            ->method('onAuthenticationRequired')
            ->willReturn($this->authFormRedirectResponse);

        $this->assertRedirectToAuthForm();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationStepSuccessfulButNotCompleted_dispatchRequireEvent()
    {
        $twoFactorToken = $this->createTwoFactorToken();
        $this->stubTokenManagerHasToken($twoFactorToken);
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubAuthenticationManagerReturnsToken($twoFactorToken); // Must be TwoFactorToken

        $this->assertEventsDispatched([
            TwoFactorAuthenticationEvents::ATTEMPT,
            TwoFactorAuthenticationEvents::SUCCESS,
            TwoFactorAuthenticationEvents::REQUIRE,
        ]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_twoFactorProcessComplete_returnResponseFromSuccessHandler()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubAuthenticationManagerReturnsToken($this->createMock(TokenInterface::class)); // Not a TwoFactorToken

        $response = $this->createMock(Response::class);
        $this->successHandler
            ->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->willReturn($response);

        $this->getResponseEvent
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->identicalTo($response));

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_twoFactorProcessComplete_dispatchCompleteEvent()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubAuthenticationManagerReturnsToken($this->createMock(TokenInterface::class)); // Not a TwoFactorToken
        $this->stubHandlersReturnResponse();

        $this->assertEventsDispatched([
            TwoFactorAuthenticationEvents::ATTEMPT,
            TwoFactorAuthenticationEvents::SUCCESS,
            TwoFactorAuthenticationEvents::COMPLETE,
        ]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_twoFactorProcessCompleteWithTrustedEnabled_setTrustedDevice()
    {
        $authenticatedToken = $this->createMock(TokenInterface::class);
        $authenticatedToken
            ->expects($this->any())
            ->method('getUser')
            ->willReturn('user');

        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubRequestHasParameter(self::TRUSTED_PARAM, '1');
        $this->stubAuthenticationManagerReturnsToken($authenticatedToken); // Not a TwoFactorToken
        $this->stubHandlersReturnResponse();

        $this->trustedDeviceManager
            ->expects($this->once())
            ->method('addTrustedDevice')
            ->with('user', 'firewallName');

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_twoFactorProcessCompleteWithTrustedDisabled_notSetTrustedDevice()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubCsrfTokenValidatorHasValidCsrfTokenReturnsTrue();
        $this->stubRequestHasParameter(self::TRUSTED_PARAM, '0');
        $this->stubAuthenticationManagerReturnsToken($this->createMock(TokenInterface::class)); // Not a TwoFactorToken
        $this->stubHandlersReturnResponse();

        $this->trustedDeviceManager
            ->expects($this->never())
            ->method($this->anything());

        $this->listener->handle($this->getResponseEvent);
    }
}
