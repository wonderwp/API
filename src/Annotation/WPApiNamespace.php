<?php

namespace WonderWp\Component\API\Annotation;

#[\Attribute(\Attribute::TARGET_CLASS)]
class WPApiNamespace
{
    public function __construct(
        public ?string $namespace = null,
    ) {
    }
}
