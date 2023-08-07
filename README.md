# Laravel blade icons

This package makes it super simple to embed any svg icons in your code. It downloads the svg from the right place automatically from github and caches it locally.

<img width="1360" alt="icons" src="https://github.com/blinq-dev/laravel-blade-icons/assets/168357/2ad43a5e-1aed-4da7-a3e4-f6e1d7ad9f81">

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

That's it! The first time it downloads the icon to your resources folder and after that it loads from there.

## Configuration

Optionally, you can publish the config file using:

```bash
php artisan vendor:publish --tag="blinq-icons:config"
```

This is the contents of the published config file:

```php
return [
    /**
     * Set the download path for the icons
     */
    'download_path' => base_path('resources/svg'),
    /**
     * Sets the prefix for the blade components
     * x-icon becomes x-prefix-icon
     */
    'prefix' => null,
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="blinq-icons:views"
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Lennard](https://github.com/blinq-dev)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
