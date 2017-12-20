<?php

namespace Thinktomorrow\Locale\Detectors;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Scopes\Scope;

class HiddenSegmentDetector implements Detector
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get(Scope $scope, Config $config): ?Locale
    {
        // If a locale segment is found, it means locale is not hidden so we won't bother going further.
        if ($this->segment($scope)) return null;

        // At this point if no locale segment is given and if in config the hidden_locale is set,
        // we assume the locale is hidden.
        // TODO account for domain specific hidden (default) locales
        return $scope->default();
    }

    public function segment(Scope $scope): ?Locale
    {
        $segment = $this->request->segment(1);

        return $scope->get($segment);
    }
}