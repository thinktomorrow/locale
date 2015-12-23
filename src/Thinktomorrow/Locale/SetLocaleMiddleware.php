<?php

namespace Thinktomorrow\Locale;

use Closure;

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
         * 1) If locale is in request (url segment /fr/home), this locale will be forced
         * 2) If locale is in request (url query param e.g. ?lang=fr),
         * 3) Default: get locale from cookie
         * 4) Otherwise: set locale to our fallback language
         */
        app()->make(Locale::class)->set();

        return $next($request);
    }
}
