<?php

namespace Thinktomorrow\Locale\Detectors;

use Thinktomorrow\Locale\Locale;
use Thinktomorrow\Locale\Services\Scope;

class FallbackDetector implements Detector
{
    public function get(Scope $scope, array $options): ?Locale
    {
        return $scope->default();
    }
}