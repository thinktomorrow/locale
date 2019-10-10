# locale

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

## Requirements
The package requires a php version of `7.1.3` or more. Laravel version `5.6.*` and upwards is supported.

## Installation

The package can be installed via Composer.
``` bash
$ composer require thinktomorrow/locale
```

The package service provider will be autodiscovered by laravel.

To publish the config file to `/config/thinktomorrow/locale.php`, run:
``` bash
    php artisan vendor:publish --provider="Thinktomorrow\Locale\LocaleServiceProvider"
```

These are the config defaults:

## Setup

Let's say you want to support two locales: nl and en, where nl is the default locale. Here's how you would configure this:
```php
# config/thinktomorrow/locale.php

'locales' => [
        '*' => [
            'en' => 'en',
            '/'  => 'nl',
        ],
    ],
```

Some important things to note here:
- The key of each entry represents the uri segment whereas the value is the application locale.
- The * acts as a wildcard group, which means any *host* will match. This is called the default *scope* but more on scopes later on.
- The default locale has a forward slash '/' as its key. This is a required item. It is the locale when no specific uri segment matches.

### Segments
The package allows for two ways to identify the locale in a request url. Either via path segments or via the host. Let's take a moment to introduce these concepts.
A locale segment is the uri path identifier of a specific locale. E.g. *example.com/nl* has *nl* as a locale segment since it identifies the locale in the given request uri..
This is the most common way of determining locales via the incoming request. This is also sufficient when your application only has to deal with one domain root.

The configuration for this is the most basic setup where you give a list of locales under the default scope, like the config example from above.

### Scopes
A scope is a higher level identifier for a group of locales. Generally, you can compare a scope with the host part of the request url.
A scope bundles one or more of these segments together.

Let's say we want to allow *example.com* and *fr.example.com*, where the first host localises in nl and the latter in fr. We can provide the following configuration:
```php
# config/thinktomorrow/locale.php

'locales' => [
        'fr.example.com' => 'fr',
        '*' => 'nl',
    ],
```

- If the scope detects only one locale, it can be entered as a string, instead of an array.
- The more specific scope should be placed first, because matching is performed from top to bottom.
- The default scope '*' is always required and because of the *first match first serve* rule, should be placed at the bottom of the list.




If your application is visitable by only one domain root, which is the case for most apps,
you are good to go with the default scope.
All available application locales are grouped by so called scopes. Each scope has its own set
     * of locales.  A scope which can be compared with domains. Each scope
     * could represent a domain and its supported locales. Each scope entry consists of a key as the
     * pattern identifier and an array of locales. Matches are done from top to bottom so declare
     * the more specific hosts above general ones.

## Usage

To make your routes localized, place them inside a Route::group() with a following prefix:

``` php
    
    Route::group(['prefix' => localeRoutePrefix()],function(){
        
        // Routes registered within this group will be localized
        
    });
    
```

The `localeRoutePrefix` function will automatically detect the active locale based on the request and will return the appropriate url segment as well.
Behind the scenes this calls `app(\Thinktomorrow\Locale\Detect::class)->detectLocale()`. This needs to be called as early as possible in your application, like in a service provider or via the given `localeRoutePrefix` in the *routes/web.php* file. It is required for the locale package to be fully functional. 

## Generating a localized url

Localisation of your routes is done automatically when <a href="https://laravel.com/docs/5.2/routing#named-routes" target="_blank">named routes</a> are being used. 
Creation of all named routes will be localized based on current locale. Quick non-obtrusive integration. 

``` php
    route('pages.about'); // prints out http://example.com/en/about (if en is the active locale)
```

To create an url with a specific locale other than the active one, you can use the `Thinktomorrow\Locale\LocaleUrl` class.

``` php
    
    // Generate localized url from uri (resolves as laravel url() function)
    localeroute('about','en'); // http://example.com/en/about
    
    // Generate localized url from named route (resolves as laravel route() function)
    localeroute('pages.about','en'); // http://example.com/en/about  
    
    // Add additional parameters as third parameter
    localeroute('products.show','en',['slug' => 'tablet'])); // http://example/en/products/tablet
    
```

**Note:** Passing the locale as 'lang' query parameter will force the locale 
*example.com/en/about?lang=nl* makes sure the request will deal with a 'nl' locale.

## Configuration
- **locales**: Whitelist of locales available for usage inside your application. 
    Basic usage:
    ```php
        'locales' => [
            '*' => [
                'nl',
                'en',
            ]
        ],
    ```

    Multi-domain usage:
    ```php
        'locales' => [
            'https://awesome-domain-nl.com' => [
                '/' => 'nl',
            ],
            'https://awesome-domain-en.com' => [
                '/' => 'en',
            ]
        ],
    ```
    Each multi-domain can have multiple locale as well:
    Multi-domain usage:
    ```php
        'locales' => [
            'https://awesome-domain.com' => [
                'en'    => 'en',
                '/'     => 'nl',
            ]
        ],
    ```


- **hidden_locale**: You can set one of the available locales as 'hidden' which means any request without a locale in its uri, should be localized as this hidden locale.
For example if the hidden locale is 'nl' and the request uri is /foo/bar, this request is interpreted with the 'nl' locale. 
Note that this is best used for your main / default locale.
- **placeholder**: Explicit route placeholder for the locale. Must be used for the LocaleUrl::route()` method when multiple parameters need to be injected.

## Locale API

#### Set a new locale for current request
``` php
    Locale::set('en'); // Sets a new application locale and returns the locale slug
```

#### Get the current locale
``` php
    Locale::get(); // returns the current locale e.g. 'en';
    
    // You can pass it a locale that will only be returned if it's a valid locale
    Locale::get('fr'); // returns 'fr' is fr is an accepted locale value
    Locale::get('foobar'); // ignores the invalid locale and returns the default locale
```

#### Get the locale slug to be used for url injection
``` php
    Locale::getSlug(); // returns 'en' or null if the current locale is set to be hidden
```

#### Check if current locale is hidden
``` php
    Locale::isHidden(); // checks current or passed locale and returns boolean
```

## Testing

``` bash
$ vendor/bin/phpunit
```
For more details check out our full documentation https://thinktomorrow.github.io/package-docs/src/locale/

## Security

If you discover any security related issues, please email ben@thinktomorrow.be instead of using the issue tracker.

## Credits

- Ben Cavens <ben@thinktomorrow.be>

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/thinktomorrow/locale.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/thinktomorrow/locale/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/thinktomorrow/locale.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/thinktomorrow/locale.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/thinktomorrow/locale.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/thinktomorrow/locale
[link-travis]: https://travis-ci.org/thinktomorrow/locale
[link-scrutinizer]: https://scrutinizer-ci.com/g/thinktomorrow/locale/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/thinktomorrow/locale
[link-downloads]: https://packagist.org/packages/thinktomorrow/locale
[link-author]: https://github.com/bencavens
