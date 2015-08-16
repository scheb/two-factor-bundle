<?php

namespace Scheb\TwoFactorBundle\Twig;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class TwoFactorBundleExtension
 * @package Scheb\TwoFactorBundle\Twig
 * @author Dennis Fridrich, fridrich.dennis@gmail.com
 */
class TwoFactorBundleExtension extends \Twig_Extension
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorageInterface;

    /**
     * @param GoogleAuthenticator $googleAuthenticator
     * @param TokenStorageInterface $tokenStorageInterface
     */
    public function __construct(GoogleAuthenticator $googleAuthenticator, TokenStorageInterface $tokenStorageInterface)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->tokenStorageInterface = $tokenStorageInterface;
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
            $user = $this->tokenStorageInterface->getToken();
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