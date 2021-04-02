<?php

namespace WonderWp\Component\API;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use WonderWp\Component\API\Annotation\WPApiEndpoint;
use WonderWp\Component\API\Annotation\WPApiNamespace;
use WonderWp\Component\HttpFoundation\Request;
use WonderWp\Component\HttpFoundation\Result;
use WonderWp\Component\Service\AbstractService;

abstract class AbstractApiService extends AbstractService implements ApiServiceInterface
{
    /** @var Request */
    protected $request;

    protected function enableErrors()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'on');
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

            $apiNamespaceAnnotation = $reader->getClassAnnotation($reflection, WPApiNamespace::class);

            $apiEndpointAnnotation = $reader->getMethodAnnotation($reflection->getMethod($method), WPApiEndpoint::class);

            if ($apiEndpointAnnotation !== null) {
                $this->registerWPApiEndpoint($apiNamespaceAnnotation, $apiEndpointAnnotation, $reflection->getName(), $method);
            } else {
                $this->registerAdminEndpoint($className, $method);
            }
        }
        return $methods;
    }

    protected function executeEndPoint($method)
    {
        $this->setRequest(Request::getInstance());
        call_user_func([$this, $method]);
        wp_die();
    }

    /**
     * @return array
     */
    protected function listEndPoints()
    {
        $exclude = ['__construct', 'registerEndpoints', 'listEndPoints'];
        $methods = array_diff(get_class_methods($this), $exclude);
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
            if (!headers_sent()) {
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

    protected function registerWPApiEndpoint(WPApiNamespace $apiNamespaceAnnotation, WPApiEndpoint $apiEndpointAnnotation, $className, $method)
    {
        add_action('rest_api_init', function () use ($apiNamespaceAnnotation, $apiEndpointAnnotation, $className, $method) {
            $namespace = '';

            if ($apiEndpointAnnotation->namespace) {
                $namespace = $apiEndpointAnnotation->namespace;
            } else if ($apiNamespaceAnnotation->namespace) {
                $namespace = $apiNamespaceAnnotation->namespace;
            } else {
                throw new AnnotationException('Missing namespace');
            }

            $namespaceAndVersion = $namespace . '/' . $apiEndpointAnnotation->version;
            $handler = [$this, $method];

            $computedArgs = $this->recursiveBuildCallable($apiEndpointAnnotation->args);
            $computedArgs = $this->addRequiredArguments($computedArgs);
            $computedArgs['callback'] = $handler;

            register_rest_route($namespaceAndVersion, $apiEndpointAnnotation->url, $computedArgs);
        });
    }

    /**
     * @param string $className
     * @param $method
     */
    protected function registerAdminEndpoint($className, $method)
    {
        add_action('wp_ajax_' . $className . '.' . $method, function () use ($method) {
            $this->executeEndPoint($method);
        });

        add_action('wp_ajax_nopriv_' . $className . '.' . $method, function () use ($method) {
            $this->executeEndPoint($method);
        });
    }

    private function recursiveBuildCallable($args = []) {
        foreach ($args as $param => $arg) {
            if (is_array($arg)) {
                $args[$param] = $this->recursiveBuildCallable($arg);
            }

            if (strpos($param, '_callback') !== false) {
                if (method_exists($this, $arg)) {
                    $args[$param] = [$this, $arg];
                }
            }
        }

        return $args;
    }

    private function addRequiredArguments($args = []) {
        if (!isset($args['permission_callback'])) {
            $args['permission_callback'] = '__return_true';
        }

        return $args;
    }
}
