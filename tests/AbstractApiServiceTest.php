<?php

namespace WonderWp\Component\API;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\HttpFoundation\Request;
use WonderWp\Component\HttpFoundation\Result;
use WonderWp\Component\PluginSkeleton\AbstractManager;

class AbstractApiServiceTest extends TestCase
{
    /**
     * @var FakeApiService
     */
    private $apiService;

    public function setUp()
    {
        $request = Request::getInstance();
        $request->request->set('key1', 'value1');
        $request->request->set('key2', 'value2');
        $this->apiService = new FakeApiService();
        $this->apiService->setRequest($request);
    }

    public function testRequestParameterWithAllKeyShouldReturnAll()
    {
        $expected = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $this->assertEquals($expected, $this->apiService->requestParameter('all'));
    }

    public function testRequestParameterWithSpecificKeyShouldReturnIt()
    {
        $this->assertEquals('value1', $this->apiService->requestParameter('key1'));
    }

    public function testRegisterEndpointsShouldRegisterTestEndPoint()
    {
        $endPointsRegistered = $this->apiService->registerEndpoints();
        $this->assertEquals(in_array('testEndPoint', $endPointsRegistered), true);
    }

    public function testRespondShouldReturnResultInstance()
    {
        $result  = new Result(200);
        $result2 = $this->apiService->respond($result, '');
        $this->assertEquals($result, $result2);
    }

    public function testRespondJSONShouldechoResultInstance()
    {
        $result = new Result(200);
        ob_start();
        $this->apiService->respond($result, 'json');
        $result2 = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($result2, $result->__toString());

    }
}

class FakeApiService extends AbstractApiService
{
    public function __construct(AbstractManager $manager = null)
    {
        parent::__construct($manager);
        $this->enableErrors();
    }

    public function testEndPoint()
    {

    }
}
