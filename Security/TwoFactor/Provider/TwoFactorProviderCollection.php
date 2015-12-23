<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

/**
 * Class TwoFactorProviderCollection.
 */
class TwoFactorProviderCollection
{
    /**
     * @var array
     */
    protected $providers = array();

    /**
     * addProvider.
     *
     * @param string $name
     * @param mixed  $provider
     */
    public function addProvider($name, $provider)
    {
        $this->providers[$name] = $provider;
    }

    /**
     * getProviders.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }
}
