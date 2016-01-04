<?php

namespace Thinktomorrow\Locale;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LanguageSwitchController
{
    /**
     * Request to this controller will set a new preferred language locale and
     * redirect the user back to the current page in the new language
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $locale_slug = $request->get('locale');

        if ($locale_slug and false !== array_search($locale_slug, config('thinktomorrow.locale.available_locales')))
        {
            app()->make(Locale::class)->set($locale_slug);

            $cookie = Cookie::forever('locale', $locale_slug);
            Cookie::queue($cookie);

            $routename = $this->getRoutename();

            return redirect()->route($routename);
        }

        return redirect()->back();
    }

    /**
     * @return string
     */
    protected function getRoutename()
    {
        $routename = Session::get('_thinktomorrow.locale.previous_routename', config('thinktomorrow.locale.fallback_routename'));

        if (!$routename) $routename = config('thinktomorrow.locale.fallback_routename');

        // Remove our appended flag of duplicate route
        $routename = str_replace('.localefallback', '', $routename);

        return $routename;
    }
}
