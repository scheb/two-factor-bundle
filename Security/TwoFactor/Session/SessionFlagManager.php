<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SessionFlagManager
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var SessionFlagGenerator
     */
    private $flagGenerator;

    /**
     * Construct a manager that takes care of session flags.
     *
     * @param SessionInterface     $session
     * @param SessionFlagGenerator $flagGenerator
     */
    public function __construct(SessionInterface $session, SessionFlagGenerator $flagGenerator)
    {
        $this->session = $session;
        $this->flagGenerator = $flagGenerator;
    }

    /**
     * Set session flag to ask for two-factor authentication.
     *
     * @param string         $provider
     * @param TokenInterface $token
     */
    public function setBegin($provider, TokenInterface $token)
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);
        $this->session->set($sessionFlag, false);
    }

    /**
     * Set session flag completed.
     *
     * @param string         $provider
     * @param TokenInterface $token
     */
    public function setComplete($provider, TokenInterface $token)
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);

        return $this->session->set($sessionFlag, true);
    }

    /**
     * Check if session flag is set and is not complete.
     *
     * @param string         $provider
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function isNotAuthenticated($provider, TokenInterface $token)
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);

        return $this->session->has($sessionFlag) && !$this->session->get($sessionFlag);
    }

    /**
     * Generate session token.
     *
     * @param  string Two-factor provider name
     * @param TokenInterface $token
     *
     * @return string
     */
    protected function getSessionFlag($provider, TokenInterface $token)
    {
        return $this->flagGenerator->getSessionFlag($provider, $token);
    }
}
