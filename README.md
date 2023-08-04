# Laravel blade icons

This package makes it super simple to embed svg icons in your code. It downloads the proper svg automatically from github and caches it locally.

## Installation

You can install the package via composer:

```bash
composer require blinq/icons
```

## Usage
Go to https://icons.blinq.dev and find the icon you want to use.

```php
<x-icon pack='hero2/outline' name='banknotes' class='w-6 h-6' />

<x-icon pack='fa6/regular' name='eye' class='w-6 h-6' />

<x-icon pack='material/twotone' name='account_circle' class='w-6 h-6' />
```

## Configuration

Publish the config file using:

```bash
php artisan vendor:publish --tag="blinq-icons:config"
```


This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="icons-views"
```

## Usage



## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Lennard](https://github.com/blinq)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
