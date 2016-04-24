# locale

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A lightweight laravel-specific package to provide route localization. 
This package will determine the locale of your app based on the request url.

## Install

Via Composer

``` bash
$ composer require thinktomorrow/locale
```

Next add the provider to the providers array in the `/config/app.php` file:

``` php
    Thinktomorrow\Locale\LocaleServiceProvider::class,
```

Not required but if you want to use a facade you can add in the `config/app.php` file as well:

```php

'aliases' => [
    ...
    'Locale' => 'Thinktomorrow\Locale\LocaleFacade',
    'LocaleUrl' => 'Thinktomorrow\Locale\LocaleUrlFacade',
];
```


Finally create a configuration file to `/config/thinktomorrow/locale.php`

``` bash
    // artisan command from your laravel root
    php artisan vendor:publish --provider="Thinktomorrow\Locale\LocaleServiceProvider"
```


## Configuration
- **available_locales**: Whitelist of locales available for usage inside your application. 
- **hidden_locale**: You can set one of the available locales as 'hidden' which means any request without a locale in its uri, should be localized as this hidden locale.
For example if the hidden locale is 'nl' and the request uri is /foo/bar, this request is interpreted with the 'nl' locale. 
Note that this is best used for your main / default locale.

## Usage

In order to make your routes localized, they need to be prefixed with the locale itself. You can for instance place them inside a Route::group() with a prefix
value that is determined by the Locale class itself:

```php
    
    Route::group(['prefix' => app(Thinktomorrow\Locale\Locale::class)->set()],function(){
        // Routes registered within this group will be localized
    });
    
```

**note**: *Currently only requests where the locale is set as the first path segment, are supported. Later this can be expanded to support subdomain- and tld-based localization.*

## Generating a localized url

For a clean and non-obtrusive integration with your app, it is advised to work with <a href="https://laravel.com/docs/5.2/routing#named-routes" target="_blank">named routes</a>.
This way the localisation of your routes is done automatically. 

```php
    // Creation of all named routes will be localized based on current locale
    route('pages.about'); // prints out http://example.com/en/about
```

By default all routes use the current locale. In order to force a different locale route, you can use the `Thinktomorrow\Locale\LocaleUrl` class.

```php
    
    // Generate localized url from uri (resolves as laravel url() function)
    Thinktomorrow\Locale\LocaleUrl::to('about','en'); // prints out http://example.com/en/about
    
    // Generate localized url from named route (resolves as laravel route() function)
    Thinktomorrow\Locale\LocaleUrl::route('pages.about','en'); // prints out http://example.com/en/about
   
```

Passing the locale as 'lang' query parameter will force the locale 
*example.com/en/about?lang=nl* makes sure the request will deal with a 'nl' locale.

#### Set a new locale for current request
```php
    app('Thinktomorrow\Locale\Locale')->set('en');
```

#### Get the current locale
```php
    app('Thinktomorrow\Locale\Locale')->get(); // returns 'en' and basically an alias for app()->getLocale();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

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