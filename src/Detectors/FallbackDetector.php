<?php

namespace Thinktomorrow\Locale\Detectors;

use Thinktomorrow\Locale\Services\Locale;
use Thinktomorrow\Locale\Services\Config;
use Thinktomorrow\Locale\Services\Scope;

class FallbackDetector implements Detector
{
    public function get(Scope $scope, Config $config): ?Locale
    {
        return Locale::from(app()->getLocale());
    }
}