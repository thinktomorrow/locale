<?php

namespace Thinktomorrow\Locale\Detectors;

use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Locale;

class FallbackDetector implements Detector
{
    public function get(Scope $scope, Config $config): ?Locale
    {
        return Locale::from(app()->getLocale());
    }
}
