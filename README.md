# Locale
Standardise the logic for using localization in routing. 

## Logic
- Application links will be auto-injected with the proper locale. 
- This could be as url segment such as /nl/example
- Locale is also kept in a cookie
- Language can be forced overridden with query parameter ?lang=fr. Usefull for quick testing
- You should use a named route since only the named routes are being localised. This means all the route() calls. So url(), or redirect()->to() and such are not being influenced.


## Installation
Include the package via composer:
`composer require thinktomorrow/locale`

Connect the package to the Laravel framework by adding the provider to the providers array in the /config/app.php file
`Thinktomorrow\Locale\LocaleServiceProvider`

Publish the configuration options to /config/thinktomorrow/locale.php
`php artisan vendor:publish`

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