<?php

declare(strict_types=1);

namespace WonderWp\Component\API\Tests\Fixtures;

use WonderWp\Component\API\AbstractApiService;
use WonderWp\Component\API\Annotation\WPApiEndpoint;
use WonderWp\Component\API\Annotation\WPApiNamespace;

#[WPApiNamespace(namespace: 'wwp-deny')]
class DeniedPermissionApiService extends AbstractApiService
{
    public function isRequestAuthorized(): bool
    {
        return false;
    }

    #[WPApiEndpoint(
        url: '/secret',
        args: [
            'methods' => 'GET',
            'permission_callback' => 'isRequestAuthorized',
        ]
    )]
    public function secret(): array
    {
        return ['secret' => true];
    }
}
