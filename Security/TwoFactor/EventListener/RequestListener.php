<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener
{
    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface
     */
    private $authHandler;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array
     */
    private $supportedTokens;

    /**
     * @var string
     */
    private $excludePattern;

    /**
     * Construct a listener for login events.
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface            $authHandler
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param array                                                                               $supportedTokens
     * @param string                                                                              $excludePattern
     */
    public function __construct(AuthenticationHandlerInterface $authHandler, TokenStorageInterface $tokenStorage, array $supportedTokens, $excludePattern)
    {
        $this->authHandler = $authHandler;
        $this->tokenStorage = $tokenStorage;
        $this->supportedTokens = $supportedTokens;
        $this->excludePattern = $excludePattern;
    }

    /**
     * Listen for request events.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Exclude path
        if ($this->excludePattern !== null && preg_match('#'.$this->excludePattern.'#', $request->getPathInfo())) {
            return;
        }

        // Check if security token is supported
        $token = $this->tokenStorage->getToken();
        if (!$this->isTokenSupported($token)) {
            return;
        }

        // Forward to two-factor provider
        // Providers can create a response object
        $context = new AuthenticationContext($request, $token);
        $response = $this->authHandler->requestAuthenticationCode($context);

        // Set the response (if there is one)
        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    /**
     * Check if the token class is supported.
     *
     * @param mixed $token
     *
     * @return bool
     */
    private function isTokenSupported($token)
    {
        $class = get_class($token);

        return in_array($class, $this->supportedTokens);
    }
}
