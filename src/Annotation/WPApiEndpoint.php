<?php

namespace WonderWp\Component\API\Annotation;


/**
 * @Annotation
 * @Target({"METHOD"})
 */
class WPApiEndpoint
{
    /**
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
     * @var array
     */
    public $args = [];
}
