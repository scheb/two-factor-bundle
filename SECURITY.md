Security Policy
===============

Reporting a Vulnerability
-------------------------

If you think that you have found a security issue in the bundle, don't use the bug tracker and don't publish it
publicly. Instead, please report via email to me@christianscheb.de.

Supported Versions
------------------

See the "Version Guidance" section in [README.md](README.md).

Known Security Issues
---------------------

- Before version 3.7 the bundle is vulnerable to a
[security issue in JWT](https://auth0.com/blog/critical-vulnerabilities-in-json-web-token-libraries/), which can be
exploited by an attacker to generate trusted device cookies on their own, effectively by-passing two-factor
authentication. ([#143](https://github.com/scheb/two-factor-bundle/issues/143))

- Before versions 3.26.0 / 4.11.0 it was possible to bypass two-factor authentication when the remember-me option is
available on the login form. ([#253](https://github.com/scheb/two-factor-bundle/issues/253))
