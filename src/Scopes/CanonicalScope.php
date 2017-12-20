<?php

namespace Thinktomorrow\Locale\Scopes;

use Thinktomorrow\Locale\Values\Root;

class CanonicalScope extends Scope
{
    /**
     * When the canonical scope has a root set to be
     * other than the current, that specific root is defined here
     * By default the current request root is of use (NULL)
     *
     * @var null|Root
     */
    private $root = null;

    public function setRoot(Root $root)
    {
        $this->root = $root;

        return $this;
    }

    public function root(): ?Root
    {
        return $this->root;
    }
}