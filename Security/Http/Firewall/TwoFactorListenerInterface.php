<?php

namespace Scheb\TwoFactorBundle\Security\Http\Firewall;

use Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory;

interface TwoFactorListenerInterface
{
    public const DEFAULT_OPTIONS = [
        'auth_form_path' => TwoFactorFactory::DEFAULT_AUTH_FORM_PATH,
        'check_path' => TwoFactorFactory::DEFAULT_CHECK_PATH,
        'auth_code_parameter_name' => TwoFactorFactory::DEFAULT_AUTH_CODE_PARAMETER_NAME,
        'trusted_parameter_name' => TwoFactorFactory::DEFAULT_TRUSTED_PARAMETER_NAME,
    ];
}
