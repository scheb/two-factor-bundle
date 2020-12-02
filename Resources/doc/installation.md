Installation
============

## Prerequisites

If you're using anything other than Doctrine ORM to manage the user entity you will have to implement a
[persister service](persister.md).

## Installation

### Step 1: Install with Composer

Add this bundle via Composer:

```bash
composer require scheb/two-factor-bundle
```

### Step 2: Enable the bundle

Enable this bundle in your `config/bundles.php`:

```php
<?php

return [
	// ...
    Scheb\TwoFactorBundle\SchebTwoFactorBundle::class => ['all' => true],
];
```

### Step 3: Define routes

In `config/routes.yaml` add a route for the two-factor authentication form and another one for checking the
authentication code. The routes must be **located within the path `pattern` of the firewall**, the one which uses
two-factor authentication.

```yaml
# config/routes.yaml
2fa_login:
    path: /2fa
    defaults:
        # "scheb_two_factor.form_controller" references the controller service provided by the bundle.
        # You don't HAVE to use it, but - except you have very special requirements - it is recommended.
        _controller: "scheb_two_factor.form_controller:form"

2fa_login_check:
    path: /2fa_check
```

If you have multiple firewalls with two-factor authentication, each one needs its own set of login and
check routes that must be located within the associated firewall's path `pattern`.

### Step 4: Configure the firewall

Enable two-factor authentication **per firewall** and configure `access_control` for the 2fa routes:

```yaml
# config/packages/security.yaml
security:
    firewalls:
        your_firewall_name:
            two_factor:
                auth_form_path: 2fa_login    # The route name you have used in the routes.yaml
                check_path: 2fa_login_check  # The route name you have used in the routes.yaml

    # The path patterns shown here have to be updated according to your routes.
    # IMPORTANT: ADD THESE ACCESS CONTROL RULES AT THE VERY TOP OF THE LIST!
    access_control:
        # This makes the logout route accessible during two-factor authentication. Allows the user to
        # cancel two-factor authentication, if they need to.
        - { path: ^/logout, role: IS_AUTHENTICATED_ANONYMOUSLY }
        # This ensures that the form can only be accessed when two-factor authentication is in progress.
        - { path: ^/2fa, role: IS_AUTHENTICATED_2FA_IN_PROGRESS }
```

More per-firewall configuration options can be found in the [configuration reference](configuration.md).

### Step 5: Configure authentication tokens

Your firewall may offer different ways how to login. By default the bundle is only listening to the user-password
authentication (which uses the token class `Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken`).
If you want to support two-factor authentication with another login method, you have to register its token class in the
`scheb_two_factor.security_tokens` configuration option.

```yaml
# config/packages/scheb_two_factor.yaml
scheb_two_factor:
    security_tokens:
        - Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
        - Acme\AuthenticationBundle\Token\CustomAuthenticationToken
```

For a guard-based authentication method, you have to configure the
`Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken` token class.

### Step 6: Enable two-factor authentication methods

The two-factor authentication methods need to be enabled separately. Read how to do this for
[Google Authenticator](providers/google.md), [TOTP Authenticator](providers/totp.md) or [email authentication](providers/email.md).

### Step 7: Detailed configuration

You probably want to configure some details of the bundle. See the [all configuration options](configuration.md).
