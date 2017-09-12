<?php

namespace Thinktomorrow\Locale\Detectors;

use Thinktomorrow\Locale\Locale;
use Thinktomorrow\Locale\Services\Scope;

interface Detector
{
    public function get(Scope $scope, array $options): ?Locale;
}