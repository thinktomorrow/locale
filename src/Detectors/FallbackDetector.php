<?php

namespace Thinktomorrow\Locale\Detectors;

use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Scopes\Scope;

class FallbackDetector implements Detector
{
    public function get(Scope $scope, Config $config): ?Locale
    {
        return Locale::from(app()->getLocale());
    }
}