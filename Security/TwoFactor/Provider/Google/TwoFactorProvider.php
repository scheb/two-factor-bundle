<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TwoFactorProvider implements TwoFactorProviderInterface
{
    /**
     * @var CodeValidatorInterface
     */
    private $authenticator;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var string
     */
    private $formTemplate;

    /**
     * @var string
     */
    private $authCodeParameter;

    /**
     * Construct provider for Google authentication.
     *
     * @param CodeValidatorInterface $authenticator
     * @param string                 $formTemplate
     * @param string                 $authCodeParameter
     */
    public function __construct(CodeValidatorInterface $authenticator, $formTemplate, $authCodeParameter)
    {
        $this->authenticator = $authenticator;
        $this->formTemplate = $formTemplate;
        $this->authCodeParameter = $authCodeParameter;
    }

    /**
     * Set templating engin to avoid cirular reference when injecting in the constructor.
     *
     * @param EngineInterface $templating
     */
    public function setTemplatingEngine(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Begin Google authentication process.
     *
     * @param AuthenticationContext $context
     *
     * @return bool
     */
    public function beginAuthentication(AuthenticationContext $context)
    {
        // Check if user can do email authentication
        $user = $context->getUser();

        return $user instanceof TwoFactorInterface && $user->getGoogleAuthenticatorSecret();
    }

    /**
     * Ask for Google authentication code.
     *
     * @param AuthenticationContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context)
    {
        $user = $context->getUser();
        $request = $context->getRequest();
        $session = $context->getSession();

        // Display and process form
        $authCode = $request->get($this->authCodeParameter);
        if ($authCode !== null) {
            if ($this->authenticator->checkCode($user, $authCode)) {
                $context->setAuthenticated(true);

                return new RedirectResponse($request->getUri());
            }

            $session->getFlashBag()->set('two_factor', 'scheb_two_factor.code_invalid');
        }

        // Force authentication code dialog
        return $this->templating->renderResponse($this->formTemplate, array(
            'useTrustedOption' => $context->useTrustedOption(),
        ));
    }
}
