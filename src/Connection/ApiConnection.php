<?php

namespace WonderWp\Component\API\Connection;

class ApiConnection
{
    protected string $url;
    protected string $username;
    protected string $apiKey;

    public function __construct(string $url, string $username, string $apiKey)
    {
        $this->url = $url;
        $this->username = $username;
        $this->apiKey = $apiKey;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAuthenticationHeader(): array
    {
        return [
            $this->username => $this->apiKey
        ];
    }
}
