<?php

namespace WonderWp\Component\API\Annotation;

#[\Attribute(\Attribute::TARGET_METHOD)]
class WPApiEndpoint
{
    public function __construct(
        public string $url,
        public ?string $namespace = null,
        public string $version = 'v1',
        public array $args = [],
    ) {
    }
}
