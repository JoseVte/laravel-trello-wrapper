# Laravel 8 Trello API wrapper

![Build](https://github.com/JoseVte/laravel-trello-wrapper/actions/workflows/ci.yml/badge.svg)
[![Latest Stable Version](http://poser.pugx.org/josrom/laravel-trello-wrapper/v)](https://packagist.org/packages/josrom/laravel-trello-wrapper)
[![Total Downloads](http://poser.pugx.org/josrom/laravel-trello-wrapper/downloads)](https://packagist.org/packages/josrom/laravel-trello-wrapper)
[![License](http://poser.pugx.org/josrom/laravel-trello-wrapper/license)](https://packagist.org/packages/josrom/laravel-trello-wrapper)

A simple Laravel 8 package that wraps [Trello](https://trello.com) API.

## Requirements

* PHP 7.3 or 8.0 or higher

## Installation

You can install the package using the [Composer](https://getcomposer.org/) package manager running this command in your project root:

```sh
composer require josrom/laravel-trello-wrapper
```

## Laravel

The package includes a service providers and a facade for easy integration and a nice syntax for Laravel.

### Configuration

Publish the configuration file with:

```sh
php artisan vendor:publish --provider="LaravelTrello\TrelloServiceProvider"
```

Head into the file and configure the keys and defaults you'd like the package to use.

## Usage

#### Creating a basic card

```php
$card = Trello::manager()->getCard();
$card
    ->setBoardId(Trello::getDefaultBoardId())
    ->setListId(Trello::getDefaultListId())
    ->setName('Example card')
    ->setDescription('Description of the card')
    ->save();
```

#### Creating a more complex card

```php
// Create the card
$card = Trello::manager()->getCard();
$card
    ->setBoardId(Trello::getDefaultBoardId())
    ->setListId(Trello::getDefaultListId())
    ->setName('Example card')
    ->setDescription('Description of the card')
    ->save();

// Add a checklist with one item
$checklist = Trello::manager()->getChecklist();
$checklist
    ->setCard($card)
    ->setName('Example list')
    ->save();
Trello::getChecklistApi()->items()->create($checklist->getId(), 'Example checklist item');

// Attach an image using a url
Trello::getCardApi()->attachments()->create($card->getId(), ['url' => 'http://lorempixel.com/400/200/']);
```

#### More examples

For more examples of usage, please see the original PHP Trello API package documentation: https://github.com/cdaguerre/php-trello-api

## Contributing

If you're having problems, spot a bug, or have a feature suggestion, please log and issue on Github. If you'd like to have a crack yourself, fork the package and make a pull request.
