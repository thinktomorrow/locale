<?php

namespace Thinktomorrow\Locale\Detectors;

use Thinktomorrow\Locale\Services\Locale;
use Thinktomorrow\Locale\Services\Config;
use Thinktomorrow\Locale\Services\Scope;

interface Detector
{
    public function get(Scope $scope, Config $config): ?Locale;
}