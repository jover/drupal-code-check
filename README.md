# Drupal Code Check

A Git pre-commit hook to check Drupal Coding Standards and more.

[![Latest Stable Version](https://poser.pugx.org/jover_be/drupal-code-check/v/stable)](https://packagist.org/packages/jover_be/drupal-code-check) [![Total Downloads](https://poser.pugx.org/jover_be/drupal-code-check/downloads)](https://packagist.org/packages/jover_be/drupal-code-check) [![Latest Unstable Version](https://poser.pugx.org/jover_be/drupal-code-check/v/unstable)](https://packagist.org/packages/jover_be/drupal-code-check) [![License](https://poser.pugx.org/jover_be/drupal-code-check/license)](https://packagist.org/packages/jover_be/drupal-code-check) [![composer.lock](https://poser.pugx.org/jover_be/drupal-code-check/composerlock)](https://packagist.org/packages/jover_be/drupal-code-check)

## Description

This Git pre-commit hook will be active on your Composer based Drupal project.

Things which will be checked in the pre-commit hook:

* Syntax checking using _PHP Linter_
* Automatically try to match code style via _PHP Code Sniffer Beautifier and Fixer_
* Coding standards checking using _PHP Code Sniffer_
* Blacklisted strings checking/validation

Note that files of the following origins are **not checked**:

* Drupal Core
* Contributed Modules
* Contributed Libraries
* Contributed Themes
* Contributed Profiles

## Getting started

### Prerequisites

* Composer
* Composer based Drupal project
* PHP 5.4 or higher

### Installation

Add this project as a composer dependency on your Composer based Drupal project.

```bash
composer require jover_be/drupal-code-check
```

And don't forget to update...

```bash
composer update jover_be/drupal-code-check
```

In order to activate the Git Hooks, update your composer.json file like following:

```
{
    "scripts": {
        "post-install-cmd": [
            "jover_be\\drupal_code_check\\GitHooks::create"
        ],
        "post-update-cmd": [
        	"jover_be\\drupal_code_check\\GitHooks::create",
        ]
    }
}
```

## Author

[jover.be](http://www.jover.be)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
