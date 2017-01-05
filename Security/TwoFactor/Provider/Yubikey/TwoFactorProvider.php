<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Yubikey;

use Scheb\TwoFactorBundle\Model\Yubikey\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Renderer;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeValidator;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Surfnet\YubikeyApiClientBundle\Service\VerificationService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorProvider implements TwoFactorProviderInterface
{

    /**
     * @var VerificationService
     */
    private $yubikeyService;

    /**
     * @var BackupCodeValidator
     */
    private $backupCodeValidator;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var string
     */
    private $authCodeParameter;

    public function __construct(BackupCodeValidator $backupCodeValidator, VerificationService $yubikeyService, Renderer $renderer, $authCodeParameter)
    {
        $this->backupCodeValidator = $backupCodeValidator;
        $this->yubikeyService = $yubikeyService;
        $this->renderer = $renderer;
        $this->authCodeParameter = $authCodeParameter;
    }

    /**
     * Begin Yubikey authentication process.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return bool
     */
    public function beginAuthentication(AuthenticationContextInterface $context)
    {
        // Check if user can do email authentication
        $user = $context->getUser();

        return $user instanceof TwoFactorInterface && $user->getYubikeyId();
    }

    /**
     * Ask for Yubikey authentication code.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return Response|null
     */
    public function requestAuthenticationCode(AuthenticationContextInterface $context)
    {
        $user = $context->getUser();
        $request = $context->getRequest();
        $session = $context->getSession();

        //check if a code has been received
        $authCode = $request->get($this->authCodeParameter);
        if ($authCode !== null) {

            $yubikeyProvidedIdentifier = substr($authCode, -0, 12);

            //if the yubikey matches the one we have for this user
            if($yubikeyProvidedIdentifier == $user->getYubikeyId()) {

                //submit to the yubikey server
                $otp = Otp::fromString($authCode);
                $result = $this->yubikeyService->verify($otp);

                //if the server has valided the token
                if ($result->isSuccessful()) {

                    $context->setAuthenticated(true);

                    return new RedirectResponse($request->getUri());

                }

                //if not yubikey, is it a backup code ?
            } else if ($user instanceof BackupCodeInterface && $user->isBackupCode($authCode)) {

                //backup code has been validated
                if($this->backupCodeValidator->checkCode($user, $authCode)) {

                    $context->setAuthenticated(true);

                    return new RedirectResponse($request->getUri());

                }

            }

            //else, error message
            $session->getFlashBag()->set('two_factor', 'scheb_two_factor.code_invalid');
        }

        // Force authentication code dialog
        return $this->renderer->render($context);
    }
}
