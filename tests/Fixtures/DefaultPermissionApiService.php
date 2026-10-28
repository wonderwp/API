<?php

declare(strict_types=1);

namespace WonderWp\Component\API\Tests\Fixtures;

use WonderWp\Component\API\AbstractApiService;
use WonderWp\Component\API\Annotation\WPApiEndpoint;
use WonderWp\Component\API\Annotation\WPApiNamespace;

#[WPApiNamespace(namespace: 'wwp-perm')]
class DefaultPermissionApiService extends AbstractApiService
{
    #[WPApiEndpoint(url: '/open', args: ['methods' => 'GET'])]
    public function open(): void
    {
    }
}
