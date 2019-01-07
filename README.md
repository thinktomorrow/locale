# locale

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

## Install

Via Composer

``` bash
$ composer require thinktomorrow/locale
```

The package will be autodiscovered by laravel so no need to add the provider to the config/app.php file.

Finally create a configuration file to `/config/thinktomorrow/locale.php`

``` bash
    php artisan vendor:publish --provider="Thinktomorrow\Locale\LocaleServiceProvider"
```

## Usage

To make your routes localized, place them inside a Route::group() with a following prefix:

``` php
    
    Route::group(['prefix' => localeRoutePrefix()],function(){
        
        // Routes registered within this group will be localized
        
    });
    
```


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
