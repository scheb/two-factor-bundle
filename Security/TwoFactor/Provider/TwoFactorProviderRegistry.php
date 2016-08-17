<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthFailureEvent;

class TwoFactorProviderRegistry implements AuthenticationHandlerInterface
{
    /**
     * Manages session flags.
     *
     * @var SessionFlagManager
     */
    private $flagManager;

    /**
     * List of two-factor providers.
     *
     * @var array
     */
    private $providers;

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Request parameter name used for code.
     *
     * @var string
     */
    protected $authRequestParameter;

    /**
     * Initialize with an array of registered two-factor providers.
     *
     * @param SessionFlagManager       $flagManager
     * @param array                    $providers
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $authRequestParameter
     */
    public function __construct(SessionFlagManager $flagManager, $providers = [], EventDispatcherInterface $eventDispatcher = null, $authRequestParameter = '_auth_code')
    {
        $this->flagManager = $flagManager;
        $this->providers   = $providers;
        $this->eventDispatcher = $eventDispatcher;
        $this->authRequestParameter = $authRequestParameter;
    }

    /**
     * Iterate over two-factor providers and begin the two-factor authentication process.
     *
     * @param AuthenticationContextInterface $context
     */
    public function beginAuthentication(AuthenticationContextInterface $context)
    {
        /** @var TwoFactorProviderInterface $provider */
        foreach ($this->providers as $providerName => $provider) {
            if ($provider->beginAuthentication($context)) {
                $this->flagManager->setBegin($providerName, $context->getToken());
            }
        }
    }

    /**
     * Iterate over two-factor providers and ask for two-factor authentication.
     * Each provider can return a response. The first response will be returned.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return Response|null
     */
    public function requestAuthenticationCode(AuthenticationContextInterface $context)
    {
        $token = $context->getToken();

        // Iterate over two-factor providers and ask for completion
        /** @var TwoFactorProviderInterface $provider */
        foreach ($this->providers as $providerName => $provider) {
            if ($this->flagManager->isNotAuthenticated($providerName, $token)) {
                $response = $provider->requestAuthenticationCode($context);

                // Set authentication completed
                if ($context->isAuthenticated()) {
                    if (null !== $this->eventDispatcher) {
                        $this->eventDispatcher->dispatch(TwoFactorAuthEvent::NAME, new TwoFactorAuthEvent());
                    }
                    $this->flagManager->setComplete($providerName, $token);
                } else {
                    if (null !== $this->eventDispatcher && $context->getRequest()->request->has($this->authRequestParameter)) {
                        $this->eventDispatcher->dispatch(TwoFactorAuthFailureEvent::NAME, new TwoFactorAuthFailureEvent());
                    }
                }

                // Return response
                if ($response instanceof Response) {
                    return $response;
                }
            }
        }

        return null;
    }
}
