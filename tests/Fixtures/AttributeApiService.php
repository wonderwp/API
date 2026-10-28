<?php

declare(strict_types=1);

namespace WonderWp\Component\API\Tests\Fixtures;

use WonderWp\Component\API\AbstractApiService;
use WonderWp\Component\API\Annotation\WPApiEndpoint;
use WonderWp\Component\API\Annotation\WPApiNamespace;

#[WPApiNamespace(namespace: 'wwp-test')]
class AttributeApiService extends AbstractApiService
{
    public function isRequestAuthorized(): bool
    {
        return true;
    }

    #[WPApiEndpoint(
        url: '/ping',
        args: [
            'methods' => 'GET',
            'permission_callback' => 'isRequestAuthorized',
        ]
    )]
    public function ping(): array
    {
        return ['ok' => true];
    }

    #[WPApiEndpoint(url: '/override', namespace: 'wwp-override', version: 'v2')]
    public function overriddenNamespace(): array
    {
        return ['ok' => true];
    }

    /**
     * Legacy Doctrine-style docblock must be ignored (attributes only).
     *
     * @WPApiEndpoint(url="/from-docblock")
     */
    public function docblockOnly(): void
    {
    }

    public function adminOnly(): void
    {
    }
}
