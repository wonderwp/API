<?php

namespace WonderWp\Component\API\Response;

use WonderWp\Component\Response\AbstractResponse;

class ApiResponse extends AbstractResponse
{
    const SUCCESS = 'api_response.success';
    const ERROR = 'api_response.error';
    const UNAUTHORIZED = 'api_response.unauthorized';

    protected array $data;
    protected array $headers;

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

}
