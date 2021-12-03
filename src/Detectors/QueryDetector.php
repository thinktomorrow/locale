<?php

namespace Thinktomorrow\Locale\Detectors;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Locale;

class QueryDetector implements Detector
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
        if (! isset($config['query_key'])) {
            return null;
        }

        if (! $queryValue = $this->request->get($config['query_key'])) {
            return null;
        }

        return ($scope->validateLocale(Locale::from($queryValue))) ? Locale::from($queryValue) : null;
    }
}
