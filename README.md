scheb/two-factor-bundle
=======================

### ⚠ Unmaintained version

Please upgrade your project to a recent version. Use bundle version 5 (or newer), which is available as
`scheb/2fa-bundle` from the [scheb/2fa](https://github.com/scheb/2fa) repository.

---

This bundle provides **[two-factor authentication](https://en.wikipedia.org/wiki/Multi-factor_authentication) for your
[Symfony](https://symfony.com/) application**.

[![Build Status](https://github.com/scheb/two-factor-bundle/workflows/CI/badge.svg?branch=4.x)](https://github.com/scheb/two-factor-bundle/actions?query=workflow%3ACI+branch%3A4.x)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/badges/quality-score.png?b=4.x)](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/?branch=4.x)
[![Code Coverage](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/badges/coverage.png?b=4.x)](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/?branch=4.x)
[![Latest Stable Version](https://img.shields.io/packagist/v/scheb/two-factor-bundle)](https://packagist.org/packages/scheb/two-factor-bundle)
[![Monthly Downloads](https://img.shields.io/packagist/dm/scheb/two-factor-bundle)](https://packagist.org/packages/scheb/two-factor-bundle/stats)
[![Total Downloads](https://img.shields.io/packagist/dt/scheb/two-factor-bundle)](https://packagist.org/packages/scheb/two-factor-bundle/stats)
[![License](https://poser.pugx.org/scheb/two-factor-bundle/license.svg)](https://packagist.org/packages/scheb/two-factor-bundle)

<p align="center"><img alt="Logo" src="Resources/doc/2fa-logo.svg" /></p>

---

It comes with the following two-factor authentication methods:

- [TOTP authentication](https://en.wikipedia.org/wiki/Time-based_One-time_Password_algorithm)
- [Google Authenticator](https://en.wikipedia.org/wiki/Google_Authenticator)
- Authentication code via email

Additional features you will like:

- Interface for custom two-factor authentication methods
- Trusted IPs
- Trusted devices (once passed, no more two-factor authentication on that device)
- Single-use backup codes for when you don't have access to the second factor device
- Multi-factor authentication (more than 2 steps)
- CSRF protection
- Whitelisted routes (accessible during two-factor authentication)

Installation
-------------

```bash
composer require scheb/two-factor-bundle
```

... and follow the [installation instructions](Resources/doc/installation.md).

Documentation
-------------
Detailed documentation of all features can be found in the [Resources/doc](Resources/doc/index.md) directory.

Version Guidance
----------------

**⚠ Version 4.x is no longer maintained.**

Please upgrade your project to a recent version. Use bundle version 5 (or newer), which is available as
`scheb/2fa-bundle` from the [scheb/2fa](https://github.com/scheb/2fa) repository.

License
-------
This bundle is available under the [MIT license](LICENSE).

Security
--------
For information about the security policy and know security issues, see [SECURITY.md](SECURITY.md).

Contributing
------------
Want to contribute to this project? See [CONTRIBUTING.md](CONTRIBUTING.md).

Support Me
----------
I'm developing this library since 2014. I love to hear from people using it, giving me the motivation to keep working
on my open source projects.

If you want to let me know you're finding it useful, please consider giving it a star ⭐ on GitHub.

If you love my work and want to say thank you, you can help me out for a beer 🍻️
[via PayPal](https://paypal.me/ChristianScheb).
