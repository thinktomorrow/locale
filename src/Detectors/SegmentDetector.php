<?php

namespace Thinktomorrow\Locale\Detectors;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Services\Locale;
use Thinktomorrow\Locale\Services\Config;
use Thinktomorrow\Locale\Services\Scope;

class SegmentDetector implements Detector
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
        $segment = $this->request->segment(1);

        return $scope->get($segment);
    }
}