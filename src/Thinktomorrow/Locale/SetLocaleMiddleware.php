<?php

namespace Thinktomorrow\Locale;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Session;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * Set locale according to following priority:
         *
         * 1) If locale is in request (url query param e.g. ?lang=fr),
         * 2) If locale is in request (url segment /fr/home)
         * 3) Default: get locale from cookie
         * 4) Otherwise: set locale to our fallback language
         */
        app()->make(Locale::class)->set();

        $response = $next($request);

        // current route is only available after the global middleware is run
        // So this should be handled after all others
        $this->storeCurrentRouteInSession();

        return $response;
    }

    private function storeCurrentRouteInSession()
    {
        $routename = config('thinktomorrow.locale.fallback_route');
        $current = app('router')->getCurrentRoute();

        if($current instanceof Route and ($action = $current->getAction()))
        {
            if(isset($action['as'])) $routename = $action['as'];
        }

        Session::put('_thinktomorrow.locale.previous_routename',$routename);
    }
}
