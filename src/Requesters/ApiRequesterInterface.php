<?php

namespace WonderWp\Component\API\Requesters;

use WonderWp\Component\API\Response\ApiResponse;

interface ApiRequesterInterface
{
    const string DEFAULT_CONTENT_KEY = 'content';

    public function fetchAll(): ApiResponse;
    public function fetchBy(string $method, string $endpoint, array $endpointParams, array $requestArgs = []): ApiResponse;
}
