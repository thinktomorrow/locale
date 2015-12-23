<?php namespace Thinktomorrow\Locale;

use Illuminate\Support\ServiceProvider;

class LocaleServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->publishes([
            __DIR__.'/config/locale.php' => config_path('thinktomorrow/locale.php')
        ]);

        $config = (file_exists(config_path('thinktomorrow/locale.php'))) ? require config_path('thinktomorrow/locale.php') : require __DIR__.'/config/locale.php';

        $this->app->bind('Thinktomorrow\Locale\Locale',function($app) use ($config){

            return new Locale(app('request'),$config);

        });

        $this->registerUrlGenerator();
    }

    public function boot()
    {
        //
    }

    /**
     * Register the URL generator service.
     *
     * This allows our route(), url(), redirect()->route(),... to obey our locale policy which
     * dictates there should be an locale url segment in place
     *
     * Override the laravel default UrlGenerator with our custom one
     * [Code is taken literally from /Illuminate/Routing/RoutingServiceProvider]
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app['url'] = $this->app->share(function ($app) {
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            $url = new UrlGenerator(
                $routes, $app->rebinding(
                'request', $this->requestRebinder()
            )
            );

            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            // If the route collection is "rebound", for example, when the routes stay
            // cached for the application, we will need to rebind the routes on the
            // URL generator instance so it has the latest version of the routes.
            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }

    /**
     * Get the URL generator request rebinder.
     *
     * @return \Closure
     */
    protected function requestRebinder()
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }

}