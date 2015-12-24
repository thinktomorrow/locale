<?php

namespace Thinktomorrow\Locale;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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
        $locale = $request->get('locale');

        if ($locale and array_search($locale, config('thinktomorrow.locale.available_locales')))
        {
            $cookie = Cookie::forever('locale', $locale);
            Cookie::queue($cookie);

            if(false != config('thinktomorrow.locale.locale_segment'))
            {
                $referer = app()->make(Locale::class)->localeUrl($request->headers->get('referer'), $locale);

                return redirect()->to($referer);
            }
        }

        return redirect()->back();
    }
}
