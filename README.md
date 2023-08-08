# Laravel blade icons
One-click icons

This package makes it super simple to embed svg icons in your laravel project. No need to download entire icon sets that will bloat your code. It only grabs the icons you want and caches them locally 🚀

Available at the time or writing:
- Heroicons (mini / outline / solid)
- Font awesome (brands / regular / solid)
- Material icons (default / outlined / round / sharp / twotone)
 
Go see https://icons.blinq.dev 

<img width="1360" alt="icons" src="https://github.com/blinq-dev/laravel-blade-icons/assets/168357/2ad43a5e-1aed-4da7-a3e4-f6e1d7ad9f81">

## Installation

Simply conjure up the following in your terminal:

```bash
composer require blinq/icons
````

## Usage
Go to https://icons.blinq.dev and find the icon you want.

```php
<x-icon pack='hero2/outline' name='banknotes' class='w-6 h-6' />

<x-icon pack='fa6/regular' name='eye' class='w-6 h-6' />

<x-icon pack='material/twotone' name='account_circle' class='w-6 h-6' />
```

That's it! First time around, it'll download the icon to your resources folder, and from then on, it'll load from there.

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

## Add another icon pack & contribute

Would you like to add another icon pack? Let's use Heroicons as an example.

You can find the "academic-cap" icon here:

https://raw.githubusercontent.com/tailwindlabs/heroicons/master/src/24/outline/academic-cap.svg

Thus, the download base path is:

https://raw.githubusercontent.com/tailwindlabs/heroicons/master/src

If you want to obtain a list of all files available in this GitHub repository, you can use the following URL:

https://api.github.com/repos/tailwindlabs/heroicons/git/trees/master?recursive=1

We must then transform this list into a format that ``blinq/icons`` understands. This transformation is described in a ``discovery.json`` file, which is created automatically.

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
        ...
```

To achieve this, we need to write some code that will transform the GitHub trees response into a ``discovery.json`` file. Take a look at the ``beforeDiscoveryFileCreated`` method below.

In this case, we also need to make adjustments to the SVG contents so they work well with the classes we apply to them. You can find guidance in the ``beforeSvgFileCreated`` method:
- Remove all specified width and height, as these should be configurable by the user of the icon.
- Similarly, remove all specified colors.
- Set the fill or stroke to currentColor, allowing the user to specify it.

These operations can be consolidated into a single class that extends ``IconPack``:

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
