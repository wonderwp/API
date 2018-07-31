<?php

namespace WonderWp\Component\API;

interface ApiServiceInterface
{
    /**
     * @return array , the array of registered end points
     */
    public function registerEndpoints();
}
