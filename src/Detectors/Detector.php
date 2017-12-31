<?php

namespace Thinktomorrow\Locale\Detectors;

use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Scope;

interface Detector
{
    public function get(Scope $scope, Config $config): ?Locale;
}