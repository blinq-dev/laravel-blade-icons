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

## Contributing

Do you want to add another icon pack?

Let's pick heroicons as an example.

The academic-cap icon is found here: 
https://raw.githubusercontent.com/tailwindlabs/heroicons/master/src/24/outline/academic-cap.svg

So the download base path is:
https://raw.githubusercontent.com/tailwindlabs/heroicons/master/src

If we want a list of all files available on this github repo, we can use this url:
https://api.github.com/repos/tailwindlabs/heroicons/git/trees/master?recursive=1

We need to transform this list into a format that blinq/icons understands. This is described in a discovery.json that is created automatically:

```json
{
    "variants": [
        "mini",
        "outline",
        "solid"
    ],
    "icons": {
        "mini\/academic-cap.svg": {
            "url": "20\/solid\/academic-cap.svg"
        },
        "mini\/adjustments-horizontal.svg": {
            "url": "20\/solid\/adjustments-horizontal.svg"
        },
        ...etc
```

To get here we need to write some code to transform the github trees response into a discovery.json (see beforeDiscoveryFileCreated)

Also in this case we need to change the svg contents to play nice with the classes we apply to them: (see beforeSvgFileCreated)
- Remove all specified width / height, this should be settable by the user of the icon (with w-6 h-6 classes for example)
- Remove all specified colors, for similar reasons
- Set a currentColor fill or stroke to let the user colorize the icons by using text color css (or with text-green classes for example)

These operations can be specified in a single class that extends IconPack:

```php
<?php

namespace Blinq\Icons\Packs;

use Blinq\Icons\IconPack;
use Blinq\Icons\IconPackConfig;

/**
 * Heroicons icon pack
 * 
 * <x-icon pack="namespace/variant" name="icon name" />
 * <x-icon pack="hero2/solid" name="banknotes" />
 */
class Heroicons extends IconPack
{
    public function configure(IconPackConfig $config)
    {
        return $config
            /**
             * Some info the be showed at https://icons.blinq.dev
             */
            ->setName("Heroicons")
            ->setLicense("MIT")
            ->setDescription("Beautiful hand-crafted SVG icons, by the makers of Tailwind CSS.")
            ->setCopyright("By the makers of tailwindcss.com")
            ->setSite("https://heroicons.com")
            /**
             * The namespace is used to select this icon pack
             */
            ->setNamespace("hero2")
            ->setPath("https://raw.githubusercontent.com/tailwindlabs/heroicons/master/src")
            ->setDiscovery("https://api.github.com/repos/tailwindlabs/heroicons/git/trees/master?recursive=1")
            /**
             * The default variant is used when no variant is specified
             */
            ->setDefaultVariant("solid");
    }

    public $variantMapping = [
        '24/solid' => "solid",
        '24/outline' => "outline",
        '20/solid' => "mini",
    ];

    /**
     * Parses the github trees url and converts it into a discovery
     *
     * @param string $discoveryResponse
     * @return void
     */
    public function beforeDiscoveryFileCreated($discoveryResponse)
    {
        // Turn string into array
        $result = json_decode($discoveryResponse, true);

        // Get the tree
        $tree = $result['tree'] ?? null;

        // Filter the tree:
        $icons = collect($tree)
            // should start with src and end with .svg
            ->filter(fn($x) => str($x['path'])->startsWith('src') && str($x['path'])->endsWith('.svg'))
            // the url part of the discovery file. E.g: 20/solid/academic-cap.svg
            ->map(fn($x) => [ "url" => str($x['path'])->replaceFirst("src/", "")])
            // key by the variant and the icon.png. E.g: mini/academic-cap.svg
            ->keyBy(fn($x) => 
                ($this->variantMapping[(string) str($x['url'])->beforeLast('/')] ?? 'other') . "/" . str($x['url'])->afterLast('/')
            );

        // Create a unique list of variants
        $variants = $icons
            ->map(fn($x) =>  $this->variantMapping[(string) str($x['url'])->beforeLast('/')] ?? 'other')
            ->unique()
            ->values();

        // Combine them
        return [
            "variants" => $variants,
            "icons" => $icons,
        ];
    }
    
    public function beforeSvgFileCreated(&$localFile, $contents)
    {
        $isSolid = strpos($localFile, 'solid') !== false || strpos($localFile, 'mini') !== false;

        // Replace ALL occurences of width="??" with nothing
        $contents = preg_replace('/ width="[^"]*"/', '', $contents);
        // Replace ALL occurences of height="??" with nothing
        $contents = preg_replace('/ height="[^"]*"/', '', $contents);

        if ($isSolid) {
            // Replace all occurences of fill="??" with nothing
            $contents = preg_replace('/ fill="[^"]*"/', '', $contents);
            
            // Add fill="currentColor" after <svg
            $contents = preg_replace('/<svg/', '<svg fill="currentColor"', $contents);
        } else {
            // Replace stroke="#0F172A" stroke-width="1.5" with nothing
            $contents = preg_replace('/ stroke="[^"]*" stroke-width="[^"]*"/', '', $contents);
            // Replace fill="none" with stroke="currentColor" stroke-width="1.5"
            $contents = preg_replace('/<svg/', '<svg stroke="currentColor" stroke-width="1.5"', $contents);
        }

        // Write
        return $contents;
    }
}
```

Then register this class in a service provider:

```
IconPack::register(new Heroicons());
```

You are encouraged to create a pull request with the IconPack class, so i can add this to this repo.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Lennard](https://github.com/blinq-dev)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
