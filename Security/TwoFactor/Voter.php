<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderCollection;
use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class Voter.
 */
class Voter implements VoterInterface
{
    /**
     * @var SessionFlagManager
     **/
    protected $sessionFlagManager;

    /**
     * @var TwoFactorProviderCollection
     **/
    protected $providerCollection;

    /**
     * __construct.
     *
     * @param SessionFlagManager          $sessionFlagManager
     * @param TwoFactorProviderCollection $providers
     **/
    public function __construct(SessionFlagManager $sessionFlagManager, TwoFactorProviderCollection $providerCollection)
    {
        $this->sessionFlagManager = $sessionFlagManager;
        $this->providerCollection = $providerCollection;
    }

    /**
     * supportsClass.
     *
     * @param string $class
     *
     * @return bool true
     **/
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * supportsAttribute.
     *
     * @param string $attribute
     *
     * @return bool true
     **/
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * vote.
     *
     * @param TokenInterface $token
     * @param mixed          $object
     * @param array          $attributes
     *
     * @return mixed result
     **/
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($this->providerCollection->getProviders() as $providerName => $provider) {
            $res = $this->sessionFlagManager->isNotAuthenticated($providerName, $token);
            if (true === $res) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
