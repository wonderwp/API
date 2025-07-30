<?php

namespace WonderWp\Component\API\Traits;

use WonderWp\Component\API\Requesters\ApiRequesterInterface;

trait HasApiRequester
{
    protected ApiRequesterInterface $apiRequester;

    /**
     * @param ApiRequesterInterface $apiRequester
     */
    public function __construct(ApiRequesterInterface $apiRequester)
    {
        $this->apiRequester = $apiRequester;
    }
}
