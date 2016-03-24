<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;

class InteractiveLoginListener
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface $authHandler
     */
    private $authHandler;

    /**
     * @var array $supportedTokens
     */
    private $supportedTokens;

    /**
     * @var string $excludePattern
     */
    private $excludePattern;

    /**
     * Construct a listener for login events
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface $authHandler
     * @param array                                                                    $supportedTokens
     * @param string                                                                   $excludePattern
     */
    public function __construct(AuthenticationHandlerInterface $authHandler, array $supportedTokens, $excludePattern = null)
    {
        $this->authHandler = $authHandler;
        $this->supportedTokens = $supportedTokens;
        $this->excludePattern = $excludePattern;
    }

    /**
     * Listen for successful login events
     *
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        // Exclude path
        if ($this->excludePattern !== null && preg_match("#".$this->excludePattern."#", $request->getPathInfo())) {
            return;
        }

        // Check if security token is supported
        $token = $event->getAuthenticationToken();
        if (!$this->isTokenSupported($token)) {
            return;
        }

        // Forward to two-factor providers
        // They decide if they will do two-factor authentication
        $context = new AuthenticationContext($request, $token);
        $this->authHandler->beginAuthentication($context);
    }

    /**
     * Check if the token class is supported
     *
     * @param  mixed   $token
     * @return boolean
     */
    private function isTokenSupported($token)
    {
        $class = get_class($token);

        return in_array($class, $this->supportedTokens);
    }
}
