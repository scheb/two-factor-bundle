<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\IpWhitelist\IpWhitelistProviderInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IpWhitelistHandler implements AuthenticationHandlerInterface
{
    /**
     * @var AuthenticationHandlerInterface
     */
    private $authenticationHandler;

    /**
     * @var IpWhitelistProviderInterface
     */
    private $ipWhitelistProvider;

    public function __construct(AuthenticationHandlerInterface $authenticationHandler, IpWhitelistProviderInterface $ipWhitelistProvider)
    {
        $this->authenticationHandler = $authenticationHandler;
        $this->ipWhitelistProvider = $ipWhitelistProvider;
    }

    public function beginTwoFactorAuthentication(AuthenticationContextInterface $context): TokenInterface
    {
        $request = $context->getRequest();

        // Support of X-Forwarded-For header (when sitting behind a proxy or a load balancer)
        $clientIp = $request->headers->get('X-Forwarded-For');
        if (!$clientIp) {
            $clientIp = $request->getClientIp();
        }

        // Skip two-factor authentication for whitelisted IPs
        if (IpUtils::checkIp($clientIp, $this->ipWhitelistProvider->getWhitelistedIps())) {
            return $context->getToken();
        }

        return $this->authenticationHandler->beginTwoFactorAuthentication($context);
    }
}
