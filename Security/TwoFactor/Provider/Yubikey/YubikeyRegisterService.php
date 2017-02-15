<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Yubikey;

use Surfnet\YubikeyApiClientBundle\Service\VerificationService;

/**
 * Class YubikeyRegisterService
 * @author Philippe Bonvin
 */
class YubikeyRegisterService
{

    /**
     * @var VerificationService
     */
    private $yubikeyService;

    /**
     * YubikeyRegisterService constructor.
     * @param VerificationService $yubikeyService
     */
    public function __construct(VerificationService $yubikeyService)
    {
        $this->yubikeyService = $yubikeyService;
    }

    /**
     * @param string $token
     * @return string|false
     */
    public function getYubikeyId($token) {

        //validate the token to the yubikey server
        $otp = Otp::fromString($token);
        $result = $this->yubikeyService->verify($otp);

        //if the server has valided the token
        if ($result->isSuccessful()) {

            //return the yubikey identifier
            return substr($token, -0, 12);
        }

        return false;

    }

}