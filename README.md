# Locale
Standardise the logic for using localization in routing. 

There are two distinct ways of keeping track of the visitor's choice of locale. The locale can be represented in the url as 
 in the form of a domain (example.nl, example.fr), subdomain (nl.example.com, fr.example.com) or url segment (example.com/nl, example.com/fr)/
The second way is providing the localized content based on cookie. However each translation of an url page should have an unique endpoint, mostly for seo reasons.

## Determine locale on request
The locale on each request is determined by following priority:
1) example.com?lang=nl: Passing the locale via query parameter has top priority. This can be usefull to force a specific locale for testing purposes.
2) example.nl / nl.example.com / example.com/nl: Locale is in url as domain or segment. 
3) Locale that is saved in browser cookie
4) Fallback locale is used if none of the above are being met

## Locale url rendering
- All localized routes should be placed inside a routegroup. The placeholder for the locale is required.
- only named routes are auto-injected with the proper locale. This means all the route() calls. So url(), or redirect()->to() and such are not being influenced.
- Currently, there is no automatic support for language specific routeslugs such as example.com/about vs. example.com/over

## Installation
Include the package via composer:
`composer require thinktomorrow/locale`

Connect the package to the Laravel framework by adding the provider to the providers array in the /config/app.php file.
NOTE: make sure this is loaded AFTER the RouteServiceProvider!
`Thinktomorrow\Locale\LocaleServiceProvider`

Publish the configuration options to /config/thinktomorrow/locale.php
`php artisan vendor:publish`

## Quick setup
```php
$locale = app()->make('Thinktomorrow\Locale\Locale');
    
    // Set the locale on the beginning of our request
    $locale->set();

    // Get the current locale
    $locale_key = $locale->get(); // or app()->getLocale();

    // Creation of named route will be localized
    var_dump(route('example')); // prints out example.dev/nl/home
});

Route::group(['prefix' => '{locale_slug}'],function(){
    Route::get('home',['as' => 'example','uses' => function(){  }]);
});
```

## Elaborate setup

### Add route pattern
In /providers/RouteServiceProvider provide the route pattern for our locale slug. This will make sure the locale slug
is valid and matches one of the available locales as set in config/thinktomorrow/locale.php:
```php
public function boot(Router $router)
{
    // Provide the locale route pattern
    app()->make('Thinktomorrow\Locale\LocaleRoutePattern')->provide();

    parent::boot($router);
}
``

Set the locale via middleware

Add the locale_slug to your routes. Perhaps place them in a group so you have a clean separation of all
localised routes and non-localized ones.
```php 
Route::group(['prefix' => '{locale_slug}'],function(){ ... });
```
   
Bind this segment to some logic to prevent improper links to unknown pages
```php 
Route::bind('locale_slug',function($locale_slug){

    if(!$locale = app()->make('Thinktomorrow\Locale\Locale')->getOrFail($locale_slug))
    {
        return abort(404);
    }
    
    return $locale;

});
```

In order to allow the user to change his language, you can provide a form where the user can pick his desired language. 
Pass the request to following controller. The request will lead the user to back to his page but in the chosen locale.
```php
Route::post('lang',['as' => 'lang.switch','uses' => \Thinktomorrow\Locale\LanguageSwitchController::class.'@store']);
```

## Usage

Set the locale for current request based on the above priority rules:
`app()->make(Locale::class)->set();`

Get the current locale:
`app()->make(Locale::class)->get();`

Get the current locale or return false if passed locale is invalid:
`app()->make(Locale::class)->getOrFail($locale);`
