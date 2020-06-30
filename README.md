scheb/two-factor-bundle
=======================

This bundle provides **[two-factor authentication](https://en.wikipedia.org/wiki/Multi-factor_authentication) for your
[Symfony](https://symfony.com/) application**.

[![Build Status](https://travis-ci.org/scheb/two-factor-bundle.svg?branch=4.x)](https://travis-ci.org/scheb/two-factor-bundle/branches)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/badges/quality-score.png?b=4.x)](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/?branch=4.x)
[![Code Coverage](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/badges/coverage.png?b=4.x)](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/?branch=4.x)
[![Latest Stable Version](https://poser.pugx.org/scheb/two-factor-bundle/v/stable.svg)](https://packagist.org/packages/scheb/two-factor-bundle)
[![Total Downloads](https://poser.pugx.org/scheb/two-factor-bundle/downloads)](https://packagist.org/packages/scheb/two-factor-bundle)
[![License](https://poser.pugx.org/scheb/two-factor-bundle/license.svg)](https://packagist.org/packages/scheb/two-factor-bundle)

<p align="center"><img alt="Logo" src="Resources/doc/2fa-logo.svg" /></p>

ℹ️ The repository contains bundle versions 1-4, versions ≥ 5 are located in [scheb/2fa](https://github.com/scheb/2fa).

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

| Version        | Status     | Symfony Version  |
|----------------|------------|------------------|
| [1.x][v1-repo] | EOL        | >= 2.1, < 2.7    |
| [2.x][v2-repo] | EOL        | ^2.6, ^3.0, ^4.0 |
| [3.x][v3-repo] | EOL        | 3.4, ^4.0, ^5.0  |
| [4.x][v4-repo] | Maintained | 3.4, ^4.0, ^5.0  |
| [5.x][v5-repo] | Maintained | 4.4, ^5.0        |

[v1-repo]: https://github.com/scheb/two-factor-bundle/tree/1.x
[v2-repo]: https://github.com/scheb/two-factor-bundle/tree/2.x
[v3-repo]: https://github.com/scheb/two-factor-bundle/tree/3.x
[v4-repo]: https://github.com/scheb/two-factor-bundle/tree/4.x
[v5-repo]: https://github.com/scheb/2fa/tree/5.x

Security
--------
For information about the security policy and know security issues, see [SECURITY.md](SECURITY.md).

Contributing
------------
Want to contribute to this project? See [CONTRIBUTING.md](CONTRIBUTING.md).

License
-------
This bundle is available under the [MIT license](LICENSE).
