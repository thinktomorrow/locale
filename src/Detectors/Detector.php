<?php

namespace Thinktomorrow\Locale\Detectors;

use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Locale;

interface Detector
{
    public function get(Scope $scope, Config $config): ?Locale;
}
