<?php

namespace WonderWp\Component\API;

use WonderWp\Component\API\Annotation\WPApiEndpoint;
use WonderWp\Component\HttpFoundation\Request;
use WonderWp\Component\HttpFoundation\Result;
use WonderWp\Component\Service\AbstractService;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

abstract class AbstractApiService extends AbstractService implements ApiServiceInterface
{
    /** @var Request */
    protected $request;

    protected function enableErrors(){
        error_reporting(E_ALL);
        ini_set('display_errors','on');
    }

    /** @inheritdoc */
    public function registerEndpoints()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();

        $reflection = new \ReflectionClass($this);
        $className = $reflection->getShortName();

        $methods = $this->listEndPoints();
        foreach ($methods as $method) {

            $apiEndpointAnnotation = $reader->getMethodAnnotation($reflection->getMethod($method), WPApiEndpoint::class);

            if ($apiEndpointAnnotation !== null) {
                $this->registerWPApiEndpoint($apiEndpointAnnotation, $reflection->getName(), $method);
            } else {
                $this->registerAdminEndpoint($className, $method);
            }
        }
        return $methods;
    }

    protected function executeEndPoint($method){
        $this->setRequest(Request::getInstance());
        call_user_func([$this, $method]);
        wp_die();
    }

    /**
     * @return array
     */
    protected function listEndPoints(){
        $exclude   = ['__construct', 'registerEndpoints','listEndPoints'];
        $methods   = array_diff(get_class_methods($this), $exclude);
        return $methods;
    }

    /**
     * @codeCoverageIgnore
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @param Result $result
     * @param string $format
     *
     * @return Result
     */
    public function respond(Result $result, $format = 'json')
    {
        if ($format === 'json') {
            if(!headers_sent()){
                header('Content-Type: application/json');
            }
            echo $result;
        }

        return $result;
    }

    /**
     * @param string $paramName
     *
     * @return array|mixed
     */
    public function requestParameter($paramName = 'all')
    {
        if ($paramName == 'all') {
            return $this->request->request->all();
        } else {
            return $this->request->request->get($paramName);
        }
    }

    protected function registerWPApiEndpoint(WPApiEndpoint $apiEndpointAnnotation, $className, $method) {
        add_action( 'rest_api_init', function () use ($apiEndpointAnnotation, $className, $method) {
            $namespace = $apiEndpointAnnotation->namespace.'/'.$apiEndpointAnnotation->version;
            $handler = [$this, $method];

            register_rest_route( $namespace, $apiEndpointAnnotation->url, [
                'methods' => $apiEndpointAnnotation->method,
                'callback' => $handler,
                'args' => $apiEndpointAnnotation->args
            ] );
        } );
    }

    /**
     * @param string $className
     * @param $method
     */
    protected function registerAdminEndpoint( $className, $method)
    {
        add_action('wp_ajax_' . $className . '.' . $method, function () use ($method) {
            $this->executeEndPoint($method);
        });

        add_action('wp_ajax_nopriv_' . $className . '.' . $method, function () use ($method) {
            $this->executeEndPoint($method);
        });
    }
}
