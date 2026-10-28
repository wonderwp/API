<?php

declare(strict_types=1);

namespace WonderWp\Component\API\Tests\Fixtures;

use WonderWp\Component\API\AbstractApiService;
use WonderWp\Component\API\Annotation\WPApiEndpoint;

class MissingNamespaceApiService extends AbstractApiService
{
    #[WPApiEndpoint(url: '/no-ns')]
    public function noNs(): void
    {
    }
}
