<?php

namespace WonderWp\Component\API\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class WPApiNamespace
{
    /**
     * @var string
     */
    public $namespace;
}
