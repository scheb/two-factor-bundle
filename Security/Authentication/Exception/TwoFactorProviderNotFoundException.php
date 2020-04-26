<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\Security\Authentication\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TwoFactorProviderNotFoundException extends AuthenticationException
{
    public const MESSAGE_KEY = 'Two-factor provider not found.';

    /**
     * @var string|null
     */
    private $provider;

    /**
     * @return string
     */
    public function getMessageKey()
    {
        return self::MESSAGE_KEY;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        /**
         * @phpcs:disable Symfony.Formatting.BlankLineBeforeReturnSniff
         * @psalm-suppress UndefinedMethod
         */
        return serialize([
            $this->provider,
            parent::serialize(),
        ]);
        /** @phpcs:enable Symfony.Formatting.BlankLineBeforeReturnSniff */
    }

    /**
     * @param string $str
     *
     * @return void
     */
    public function unserialize($str)
    {
        list($this->provider, $parentData) = unserialize($str);
        /** @psalm-suppress UndefinedMethod */
        parent::unserialize($parentData);
    }

    public function getMessageData()
    {
        return ['{{ provider }}' => $this->provider];
    }
}
