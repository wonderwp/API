<?php

namespace WonderWp\Component\API\Requesters;

use Throwable;
use WpOrg\Requests\Utility\CaseInsensitiveDictionary;
use function WonderWp\Functions\array_merge_recursive_distinct;

abstract class ApiRequester implements ApiRequesterInterface
{
    use HasApiConnection;

    public function fetchBy(string $method, string $endpoint, array $endpointParams = [], array $requestArgs = []): ApiResponse
    {
        //Add the authentication header
        $requestHeaders = $requestArgs['headers'] ?? [];
        $requestHeaders = array_merge($requestHeaders, $this->connection->getAuthenticationHeader());
        $responseHeaders = [];

        try {
            //Set the default request arguments
            $computedArgs = [
                "headers" => $requestHeaders,
                "method" => $method
            ];
            $requestArgs = array_merge_recursive_distinct($computedArgs, $requestArgs);

            $url = $this->connection->getUrl();
            $endpoint = str_replace($url, '', $endpoint);
            $url = rtrim($url, '/') . '/' . ltrim($endpoint, '/');

            //Set the request body if we are using POST or PUT
            if (in_array($method, ['POST', 'PUT'])) {
                $requestArgs["body"] = json_encode($endpointParams);
                $requestArgs["headers"]["Content-Type"] = "application/json";
            } elseif (!empty($endpointParams)) {
                $url .= "?" . http_build_query($endpointParams);
            }

            $response = wp_remote_request($url, $requestArgs);

            if (is_wp_error($response)) {
                $responseErrorCode = $response->get_error_code();
                if (!is_int($responseErrorCode)) {
                    $responseErrorCode = 500;
                }
                throw new ImportException($response->get_error_message(), $responseErrorCode);
            }

            $code = wp_remote_retrieve_response_code($response);
            if (!str_starts_with((string)$code, '2')) {
                $msg = wp_remote_retrieve_response_message($response);
                throw new ImportException(sprintf('Invalid response code [%s] %s for request %s', $code, $msg, $url), $code);
            }

            $body = wp_remote_retrieve_body($response);
            if (!empty($requestArgs)
                && !empty($requestArgs['headers'])
                && !empty($requestArgs["headers"]["Content-Type"])
                && str_contains($requestArgs["headers"]["Content-Type"], 'application/json')
            ) {
                $data = $body ? json_decode($body, true) : null;
            } else {
                if (is_string($body)) {
                    $body = [self::DEFAULT_CONTENT_KEY => $body];
                }
                $data = $body;
            }
            $responseHeaders = wp_remote_retrieve_headers($response);

            if (empty($data) && !empty($body)) {
                $errorCode = 500;
                $errorMsg = 'Invalid response body.';
                if (!empty(json_last_error_msg())) {
                    $errorCode = json_last_error();
                    $errorMsg .= ' Json decode problem : ' . json_last_error_msg();
                }
                throw new ImportException($errorMsg, $errorCode);
            }

            $response = new ApiResponse();
            $response
                ->setCode($code)
                ->setData($data)
                ->setHeaders($this->formatResponseHeaders($responseHeaders));
        } catch (Throwable $e) {
            $response = new ApiResponse();
            $response
                ->setData([
                    'error' => $e->getMessage()
                ])
                ->setCode($e->getCode())
                ->setHeaders($this->formatResponseHeaders($responseHeaders))
                ->setError($e);
        }

        return $response;
    }

    protected function formatResponseHeaders(CaseInsensitiveDictionary|array $responseHeaders): array
    {
        return $responseHeaders instanceof CaseInsensitiveDictionary ? $responseHeaders->getAll() : [];
    }

}
