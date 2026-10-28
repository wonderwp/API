<?php

namespace WonderWp\Component\API;

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
        $reflection = new \ReflectionClass($this);
        $className = $reflection->getShortName();

        $apiNamespaceAttribute = $this->readAttribute($reflection, WPApiNamespace::class);

        $methods = $this->listEndPoints();
        foreach ($methods as $method) {
            $apiEndpointAttribute = $this->readAttribute($reflection->getMethod($method), WPApiEndpoint::class);

            if ($apiEndpointAttribute !== null) {
                $this->registerWPApiEndpoint($apiNamespaceAttribute, $apiEndpointAttribute, $reflection->getName(), $method);
            } else {
                $this->registerAdminEndpoint($className, $method);
            }
        }
        return $methods;
    }

    /**
     * @template T of object
     * @param \ReflectionClass<object>|\ReflectionMethod $reflector
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    private function readAttribute(\ReflectionClass|\ReflectionMethod $reflector, string $attributeClass): ?object
    {
        $attributes = $reflector->getAttributes($attributeClass);
        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
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

    protected function registerWPApiEndpoint(?WPApiNamespace $apiNamespaceAttribute, WPApiEndpoint $apiEndpointAttribute, $className, $method)
    {
        add_action('rest_api_init', function () use ($apiNamespaceAttribute, $apiEndpointAttribute, $className, $method) {
            $namespace = '';

            if ($apiEndpointAttribute->namespace) {
                $namespace = $apiEndpointAttribute->namespace;
            } elseif ($apiNamespaceAttribute && $apiNamespaceAttribute->namespace) {
                $namespace = $apiNamespaceAttribute->namespace;
            } else {
                throw new \InvalidArgumentException('Missing namespace');
            }

            $namespaceAndVersion = $namespace . '/' . $apiEndpointAttribute->version;
            $handler = [$this, $method];

            $computedArgs = $this->recursiveBuildCallable($apiEndpointAttribute->args);
            $computedArgs = $this->addRequiredArguments($computedArgs);
            $computedArgs['callback'] = $handler;

            register_rest_route($namespaceAndVersion, $apiEndpointAttribute->url, $computedArgs);
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
