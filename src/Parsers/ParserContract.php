<?php

namespace Thinktomorrow\Locale\Parsers;

use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Scopes\Scope;
use Thinktomorrow\Locale\Values\Root;

interface ParserContract
{
    /**
     * Retrieve the generated / altered url.
     *
     * @return mixed
     */
    public function get();

    /**
     * Set the base url or routename.
     *
     * @param string $url
     * @return self
     */
    public function set(string $url);

    /**
     * Force a specific root
     *
     * @param Root $root
     * @return mixed
     */
    public function forceRoot(Root $root);

    /**
     * Place locale segment in front of url path
     * e.g. /foo/bar is transformed into /en/foo/bar.
     *
     * @param string|null $localeSegment
     * @param array $available_locales
     * @return ParserContract
     */
    public function locale(string $localeSegment = null, array $available_locales);

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function parameters(array $parameters = []);

    /**
     * @param bool $secure
     *
     * @return $this
     */
    public function secure($secure = true);
}
