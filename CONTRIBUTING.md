Contributing
============
👍🎉 First off, thanks for taking the time to contribute! 🎉👍

Submitting a Bug Report
-----------------------

Before you report a bug, please check the [troubleshooting guide](Resources/doc/troubleshooting.md) first.

When creating the bug report, please follow the bug template and provide details about the Symfony and bundle version
you're using.

Creating a Pull Request
-----------------------

You're welcome to [contribute](https://github.com/scheb/two-factor-bundle/graphs/contributors) new features by creating
a pull requests or feature request in the issues section. Besides new features, [translations](Resources/translations)
are highly welcome.

For pull requests, please follow these guidelines:

- Symfony code style (use `php_cs.xml` to configure the code style in your IDE)
- PHP7.1 type hints for everything (including: return types, `void`, nullable types)
- `declare(strict_types=1)` must be used
- Please add/update test cases
- Test methods should be named `[method]_[scenario]_[expectedResult]`

### Running Quality Checks

Before you create a pull request, please make sure your changes fulfill the quality criteria:

1) Install the dependencies with `composer install`
2) Run the PHPUnit tests with `bin/phpunit`
3) Run PHP CodeSniffer with `bin/phpcs --standard=php_cs.xml --ignore=/vendor/ .`
4) Run Psalm with `bin/psalm` and address any error-level issues
5) Run [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) v3 (not provided with the library, has to be
   installed locally): `php-cs-fixer fix`
