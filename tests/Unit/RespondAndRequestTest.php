<?php

declare(strict_types=1);

namespace WonderWp\Component\API\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\API\Tests\Fixtures\AttributeApiService;
use WonderWp\Component\HttpFoundation\Request;
use WonderWp\Component\HttpFoundation\Result;

final class RespondAndRequestTest extends TestCase
{
    private AttributeApiService $apiService;

    protected function setUp(): void
    {
        WpHookSpy::reset();
        $request = Request::getInstance();
        $request->request->replace([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        $this->apiService = new AttributeApiService();
        $this->apiService->setRequest($request);
    }

    public function testRequestParameterWithAllKeyShouldReturnAll(): void
    {
        $this->assertSame(
            ['key1' => 'value1', 'key2' => 'value2'],
            $this->apiService->requestParameter('all')
        );
    }

    public function testRequestParameterWithSpecificKeyShouldReturnIt(): void
    {
        $this->assertSame('value1', $this->apiService->requestParameter('key1'));
    }

    public function testRespondShouldReturnResultInstance(): void
    {
        $result = new Result(200);
        $this->assertSame($result, $this->apiService->respond($result, ''));
    }

    public function testRespondJsonEchoesResult(): void
    {
        $result = new Result(200);
        ob_start();
        $this->apiService->respond($result, 'json');
        $output = ob_get_clean();
        $this->assertSame((string) $result, $output);
    }
}
