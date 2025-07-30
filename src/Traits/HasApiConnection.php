<?php

namespace WonderWp\Component\API\Traits;

use WonderWp\Component\API\ApiConnection;

trait HasApiConnection
{
    protected ApiConnection $connection;

    /**
     * @param ApiConnection $connection
     */
    public function __construct(ApiConnection $connection)
    {
        $this->connection = $connection;
    }

}
