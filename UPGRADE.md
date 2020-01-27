Upgrading
=========

Here's an overview if you have to do any work when upgrading.

## 4.x to 5.x

Guard-based authentication has become the preferred way of building a custom authentication provider. Therefore,
`Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken` is now configured per default in `security_tokens`
as a token to triggers two-factor authentication. If you don't want to have it automatically configured, please set
`security_tokens` in your bundle configuration.

## 3.x to 4.x

### Dependencies

The library `sonata-project/google-authenticator`, which is used to support Google Authenticator, has been replaced with
the more flexible `spomky-labs/otphp`. The way the Google Authenticator integration works does not change.

### Interface Changes

`Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface` was extended with a new method
`prepareAuthentication($user): void` which should contain all kinds of preparations that need to be done before the user
authenticates with that provider. This is where authentication codes should be generated and sent. If you have such an
implementation in the `beginAuthentication` method, consider migrating it. The advantage is, that codes would only
be sent, if that provider is actually used.

Signature of `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface::getGoogleAuthenticatorSecret()` has changed to
return a nullable string: `?string`.

Signature of `Scheb\TwoFactorBundle\Security\TwoFactor\IpWhitelist\IpWhitelistProviderInterface::getWhitelistedIps()`
has changed to take and instance of  `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface` as an
argument.

### Service Changes

The constructor of `Scheb\TwoFactorBundle\Controller\FormController` now takes an instance of
`Symfony\Component\Security\Http\Logout\LogoutUrlGenerator` as the forth argument. If you have extended the controller,
please update your service definition accordingly.

### QR Codes

Google has deprecated the Charts API, which was used to generate QR code images. Since generating the QR code with an
external service is considered a bad security practise, the method
`Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator::getUrl()` has been removed.

Use the method `getQRContent()` on that same class to retrieve the content for the QR code and use a solutions like
[endroid/qr-code-bundle](https://github.com/endroid/qr-code-bundle) to generate the QR code image yourself.

### Translations

The bundle's translations have been moved to its own domain `SchebTwoFactorBundle`. The prefix `scheb_two_factor.` has
been removed on the translation keys. Please update in your templates:

Before:
```twig
{{ "scheb_two_factor.translation_key"|trans }}
```

After:
```twig
<p>{{ "translation_key"|trans({}, 'SchebTwoFactorBundle') }}
```

## 2.x to 3.x

### Dependencies

Dropped support for Symfony < 3.4.

### Firewall integration

The `exclude_path` configuration option has been removed, please use firewall `access_control` instead.

The following public interfaces have been extended with PHP7 type hints. Please upgrade method signatures in your
implementations.
- `Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface`
- `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface`
- `Scheb\TwoFactorBundle\Model\BackupCodeInterface`
- `Scheb\TwoFactorBundle\Model\PersisterInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface`

The method `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface::create` requires
`$firewallName` as the 3rd argument.

In the two-factor provider interface `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface` the
method `requestAuthenticationCode()` has been removed and replaced by `validateAuthenticationCode()`. The method
`getFormRenderer()` must return an instance of
`Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface` to display the authentication form.
See the documentation on [how to implement a custom two-factor provider](doc/providers/custom.md) for more
details.

The configuration options `scheb_two_factor.parameter_names.auth_code` and `scheb_two_factor.parameter_names.trusted`
have been replaced. New configuration options can be set per firewall as
`security.firewalls.[firewallName].two_factor.auth_code_parameter_name` and
`security.firewalls.[firewallName].two_factor.trusted_parameter_name`.

### Google Authenticator

In the interface `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface` the method `getUsername` has been renamed to
`getGoogleAuthenticatorUsername` to avoid conflicts. The method `isGoogleAuthenticatorEnabled` has been added.

### Email code authentication

In the interface `Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface` the method `getEmail` has been renamed to
`getEmailAuthRecipient` to avoid conflicts.

### Trusted device feature

The trusted device feature no longer requires you to store trusted tokens. Instead, it is using JWT to store on a signed
cookie on the trusted device.

The configuration node `scheb_two_factor.trusted_computer` has been renamed to `scheb_two_factor.trusted_device`.

`Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManagerInterface` has been replaced by
`Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManagerInterface`. If you want to have your own
TrustedDeviceManager, provide a service implementing the interface and configure the service name in
`scheb_two_factor.trusted_device.manager`.

`Scheb\TwoFactorBundle\Model\TrustedComputerInterface` has been replaced by
`Scheb\TwoFactorBundle\Model\TrustedDeviceInterface`. The interface no longer needs to be implemented in the user
entity. The new interface only asks you to return a version number for the trusted token. This version number can be
increased to invalidate all of the users trusted devices. If you don't implement the interface, the bundle will use
version `0` for all trusted tokens.

Configuration option `scheb_two_factor.trusted_computer.cookie_lifetime` has been renamed to
`scheb_two_factor.trusted_device.lifetime`.

### Backup code feature

The backup code feature must be explicitly enabled with the `scheb_two_factor.backup_codes.enabled` configuration option.

## 1.x to 2.x

Dropped support for Symfony < 2.6 and added support for Symfony 3.x.

## 1.2.0 to 1.3.0

The internal implementation of the Google Authentication and email authentication method has been re-factored. If you
depend on internal classes, please check if your code is affected.

## 0.3.0 to 1.0.0

Changed case of parameter from `$GoogleAuthenticatorSecret` to `$googleAuthenticatorSecret` in
`Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface`

## 0.1.0 to 0.1.1

The default authentication form template requires a route `_security_logout` to be defined, that directs to your logout
URL. Alternatively you can configure a custom template.
