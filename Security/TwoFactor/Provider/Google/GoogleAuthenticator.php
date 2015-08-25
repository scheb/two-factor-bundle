<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Google\Authenticator\GoogleAuthenticator as BaseGoogleAuthenticator;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

class GoogleAuthenticator
{

    /**
     * @var string $server
     */
    private $server;

    /**
     * @var \Google\Authenticator\GoogleAuthenticator $authenticator
     */
    private $authenticator;

    /**
     * @var string
     */
    private $issuer;

    /**
     * Construct the helper service for Google Authenticator
     *
     * @param \Google\Authenticator\GoogleAuthenticator $authenticator
     * @param string                                    $server
     * @param string                                    $issuer
     */
    public function __construct(BaseGoogleAuthenticator $authenticator, $server, $issuer)
    {
        $this->authenticator = $authenticator;
        $this->server = $server;
        $this->issuer = $issuer;
    }

    /**
     * Validates the code, which was entered by the user
     *
     * @param  \Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface $user
     * @param  string                                                 $code
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code)
    {
        return $this->authenticator->checkCode($user->getGoogleAuthenticatorSecret(), $code);
    }

    /**
     * Generate the URL of a QR code, which can be scanned by Google Authenticator app
     *
     * @param  \Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface $user
     * @return string
     */
    public function getUrl(TwoFactorInterface $user)
    {
        $encoder = "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=";
        if ($this->issuer) {
            // In the event that server is null or empty, we don't want to add the "@",
            // this stops Google Authenticator showing "username@" when the server_name is not provided.
            if ($this->server !== null) {
                $urlBeforeAddition = "otpauth://totp/%s:%s@%s?secret=%s&issuer=%s";
            } else {
                $urlBeforeAddition = "otpauth://totp/%s:%s%s?secret=%s&issuer=%s";
            }

            $encoderURL = sprintf(
                $urlBeforeAddition,
                rawurlencode($this->issuer),
                rawurlencode($user->getUsername()),
                rawurlencode($this->server),
                $user->getGoogleAuthenticatorSecret(),
                rawurlencode($this->issuer)
            );
        } else {
            // In the event that server is null or empty, we don't want to add the "@",
            // this stops Google Authenticator showing "username@" when the server_name is not provided.
            if ($this->server !== null) {
                $urlBeforeAddition = "otpauth://totp/%s@%s?secret=%s";
            } else {
                $urlBeforeAddition = "otpauth://totp/%s@%s?secret=%s";
            }

            $encoderURL = sprintf(
                $urlBeforeAddition,
                rawurlencode($user->getUsername()),
                rawurlencode($this->server),
                $user->getGoogleAuthenticatorSecret()
            );
        }

        return $encoder . urlencode($encoderURL);
    }

    /**
     * Generate a new secret for Google Authenticator
     *
     * @return string
     */
    public function generateSecret()
    {
        return $this->authenticator->generateSecret();
    }
}
