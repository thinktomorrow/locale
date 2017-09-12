<?php

namespace Thinktomorrow\Locale\Detectors;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Locale;
use Thinktomorrow\Locale\Services\Scope;

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

    public function get(Scope $scope, array $options): ?Locale
    {
        // TODO: need query_key identifier from config....
        if(!isset($options['query_key'])) return null;

        if( ! $queryValue = $this->request->get($this->query_key) ) return null;

        return $scope->get($queryValue);
    }
}