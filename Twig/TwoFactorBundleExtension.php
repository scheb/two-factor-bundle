<?php

namespace Scheb\TwoFactorBundle\Twig;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class TwoFactorBundleExtension
 * @package Scheb\TwoFactorBundle\Twig
 * @author Dennis Fridrich, fridrich.dennis@gmail.com
 */
class TwoFactorBundleExtension extends \Twig_Extension
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param GoogleAuthenticator $googleAuthenticator
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(GoogleAuthenticator $googleAuthenticator, SecurityContextInterface $securityContext)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->securityContext = $securityContext;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('googleAuthenticatorQrUrl', array($this, 'googleAuthenticatorQrUrl')),
        );
    }

    /**
     * @param null $user
     * @return string
     */
    public function googleAuthenticatorQrUrl($user = null)
    {
        if ($user === null) {
            $user = $this->securityContext->getToken()->getUser();
        }

        return $this->googleAuthenticator->getUrl($user);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'two_factor_extension';
    }
}