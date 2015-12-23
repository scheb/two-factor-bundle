<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TwoFactorProvider implements TwoFactorProviderInterface
{
    /**
     * @var CodeGeneratorInterface
     */
    private $codeGenerator;

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
     * Construct provider for email authentication.
     *
     * @param CodeGeneratorInterface $codeGenerator
     * @param CodeValidatorInterface $authenticator
     * @param string                 $formTemplate
     * @param string                 $authCodeParameter
     */
    public function __construct(CodeGeneratorInterface $codeGenerator, CodeValidatorInterface $authenticator, $formTemplate, $authCodeParameter)
    {
        $this->codeGenerator = $codeGenerator;
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
     * Begin email authentication process.
     *
     * @param AuthenticationContext $context
     *
     * @return bool
     */
    public function beginAuthentication(AuthenticationContext $context)
    {
        // Check if user can do email authentication
        $user = $context->getUser();
        if ($user instanceof TwoFactorInterface && $user->isEmailAuthEnabled()) {
            // Generate and send a new security code
            $this->codeGenerator->generateAndSend($user);

            return true;
        }

        return false;
    }

    /**
     * Ask for email authentication code.
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
            } else {
                $session->getFlashBag()->set('two_factor', 'scheb_two_factor.code_invalid');
            }
        }

        // Force authentication code dialog
        return $this->templating->renderResponse($this->formTemplate, array(
            'useTrustedOption' => $context->useTrustedOption(),
        ));
    }
}
