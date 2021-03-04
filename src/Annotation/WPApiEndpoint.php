<?php

namespace WonderWp\Component\API\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class WPApiEndpoint
{
    /**
     * @Required
     * @var string
     */
    public $namespace;

    /**
     * @var string
     */
    public $version = 'v1';

    /**
     * @Required
     * @var string
     */
    public $url;

    /**
     * @Required
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $args = [];
}
