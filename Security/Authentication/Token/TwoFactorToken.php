<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\Security\Authentication\Token;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Exception\UnknownTwoFactorProviderException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TwoFactorToken implements TwoFactorTokenInterface
{
    /**
     * @var TokenInterface
     */
    private $authenticatedToken;

    /**
     * @var string|null
     */
    private $credentials;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var string[]
     */
    private $twoFactorProviders;

    public function __construct(TokenInterface $authenticatedToken, ?string $credentials, string $providerKey, array $twoFactorProviders)
    {
        $this->authenticatedToken = $authenticatedToken;
        $this->credentials = $credentials;
        $this->providerKey = $providerKey;
        $this->twoFactorProviders = $twoFactorProviders;
    }

    /**
     * @return string|\Stringable|UserInterface
     */
    public function getUser()
    {
        return $this->authenticatedToken->getUser();
    }

    /**
     * @return void
     */
    public function setUser($user)
    {
        $this->authenticatedToken->setUser($user);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->authenticatedToken->getUsername();
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return [];
    }

    // Symfony 4.3
    public function getRoleNames(): array
    {
        return $this->getRoles();
    }

    /**
     * @return string|null
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @return void
     */
    public function eraseCredentials()
    {
        $this->credentials = null;
    }

    public function getAuthenticatedToken(): TokenInterface
    {
        return $this->authenticatedToken;
    }

    public function getTwoFactorProviders(): array
    {
        return $this->twoFactorProviders;
    }

    public function preferTwoFactorProvider(string $preferredProvider): void
    {
        $this->removeTwoFactorProvider($preferredProvider);
        array_unshift($this->twoFactorProviders, $preferredProvider);
    }

    public function getCurrentTwoFactorProvider(): ?string
    {
        $first = reset($this->twoFactorProviders);

        return false !== $first ? $first : null;
    }

    public function setTwoFactorProviderComplete(string $providerName): void
    {
        $this->removeTwoFactorProvider($providerName);
    }

    private function removeTwoFactorProvider(string $providerName): void
    {
        $key = array_search($providerName, $this->twoFactorProviders);
        if (false === $key) {
            throw new UnknownTwoFactorProviderException(sprintf('Two-factor provider "%s" is not active.', $providerName));
        }
        unset($this->twoFactorProviders[$key]);
    }

    public function allTwoFactorProvidersAuthenticated(): bool
    {
        return 0 === \count($this->twoFactorProviders);
    }

    public function getProviderKey(): string
    {
        return $this->providerKey;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return true;
    }

    /**
     * @param bool $isAuthenticated
     *
     * @return void
     */
    public function setAuthenticated($isAuthenticated)
    {
        throw new \RuntimeException('Cannot change authenticated once initialized.');
    }

    // Symfony 4.3 / PHP 7.4
    public function __serialize(): array
    {
        return [$this->authenticatedToken, $this->credentials, $this->providerKey, $this->attributes, $this->twoFactorProviders];
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->__serialize());
    }

    // Symfony 4.3 / PHP 7.4
    public function __unserialize(array $data): void
    {
        [$this->authenticatedToken, $this->credentials, $this->providerKey, $this->attributes, $this->twoFactorProviders] = $data;
    }

    /**
     * @param string|array $serialized
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        /**
         * @psalm-suppress RedundantCondition
         * @psalm-suppress TypeDoesNotContainType
         */
        $this->__unserialize(\is_array($serialized) ? $serialized : unserialize($serialized));
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return \array_key_exists($name, $this->attributes);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        if (!\array_key_exists($name, $this->attributes)) {
            throw new \InvalidArgumentException(sprintf('This token has no "%s" attribute.', $name));
        }

        return $this->attributes[$name];
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername();
    }
}
