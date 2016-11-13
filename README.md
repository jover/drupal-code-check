# Drupal pre-commit

A Git pre-commit hook to check Drupal Coding Standards and more.

[![Latest Stable Version](https://poser.pugx.org/jover_be/drupal-pre-commit/v/stable)](https://packagist.org/packages/jover_be/drupal-pre-commit) [![Total Downloads](https://poser.pugx.org/jover_be/drupal-pre-commit/downloads)](https://packagist.org/packages/jover_be/drupal-pre-commit) [![Latest Unstable Version](https://poser.pugx.org/jover_be/drupal-pre-commit/v/unstable)](https://packagist.org/packages/jover_be/drupal-pre-commit) [![License](https://poser.pugx.org/jover_be/drupal-pre-commit/license)](https://packagist.org/packages/jover_be/drupal-pre-commit)

## Description

This Git pre-commit hook will be active on your Composer based Drupal project.

Things which will be checked in the pre-commit hook:

* Syntax checking using "PHP Linter"
* Automatically try to match code style via "PHP Code Sniffer Beautifier and Fixer"
* Coding standards checking using "PHP Code Sniffer"
* Blacklisted functions checking/validation

Note that files of Drupal Core & Contributed Modules, Contributed Libraries, Contributed Themes & Contributed Profiles are not being checked.

## Getting started

### Prerequisites

* Composer
* Composer based Drupal project
* PHP 5.4 or higher

### Installation

Add this project as a composer dependency on your Composer based Drupal project.

```bash
composer require jover_be/drupal-pre-commit
```

And don't forget to update...

```bash
composer update jover_be/drupal-pre-commit
```

In order to activate the Git Hooks, update your composer.json file like following:

```
{
    "scripts": {
        "post-install-cmd": [
            "jover_be\\drupal_pre_commit\\GitHooks::create"
        ],
        "post-update-cmd": [
        	"jover_be\\drupal_pre_commit\\GitHooks::create",
        ]
    }
}
```

## Author

[jover.be](http://www.jover.be)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
