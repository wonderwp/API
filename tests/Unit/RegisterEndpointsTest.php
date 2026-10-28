<?php

declare(strict_types=1);

namespace WonderWp\Component\API\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\API\Tests\Fixtures\AttributeApiService;
use WonderWp\Component\API\Tests\Fixtures\DefaultPermissionApiService;
use WonderWp\Component\API\Tests\Fixtures\MissingNamespaceApiService;

final class RegisterEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        WpHookSpy::reset();
    }

    public function testAttributeEndpointRegistersRestRouteUnderClassNamespace(): void
    {
        $service = new AttributeApiService();
        $service->registerEndpoints();
        WpHookSpy::fire('rest_api_init');

        $ping = $this->findRoute('wwp-test/v1', '/ping');
        $this->assertNotNull($ping, 'Expected REST route wwp-test/v1 /ping');
        $this->assertSame('GET', $ping['args']['methods']);
        $this->assertSame([$service, 'ping'], $ping['args']['callback']);
        $this->assertSame([$service, 'isRequestAuthorized'], $ping['args']['permission_callback']);
    }

    public function testEndpointNamespaceOverridesClassNamespace(): void
    {
        $service = new AttributeApiService();
        $service->registerEndpoints();
        WpHookSpy::fire('rest_api_init');

        $route = $this->findRoute('wwp-override/v2', '/override');
        $this->assertNotNull($route);
        $this->assertSame([$service, 'overriddenNamespace'], $route['args']['callback']);
    }

    public function testMissingPermissionCallbackDefaultsToReturnTrue(): void
    {
        $service = new DefaultPermissionApiService();
        $service->registerEndpoints();
        WpHookSpy::fire('rest_api_init');

        $route = $this->findRoute('wwp-perm/v1', '/open');
        $this->assertNotNull($route);
        $this->assertSame('__return_true', $route['args']['permission_callback']);
    }

    public function testMissingNamespaceThrowsWhenRestApiInitFires(): void
    {
        $service = new MissingNamespaceApiService();
        $service->registerEndpoints();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing namespace');
        WpHookSpy::fire('rest_api_init');
    }

    public function testMethodWithoutAttributeRegistersAdminAjaxHooks(): void
    {
        $service = new AttributeApiService();
        $service->registerEndpoints();

        $this->assertNotEmpty(WpHookSpy::actions('wp_ajax_AttributeApiService.adminOnly'));
        $this->assertNotEmpty(WpHookSpy::actions('wp_ajax_nopriv_AttributeApiService.adminOnly'));
        $this->assertNull($this->findRouteByUrl('/from-docblock'));
    }

    public function testDocblockAnnotationIsIgnoredForRestRegistration(): void
    {
        $service = new AttributeApiService();
        $service->registerEndpoints();
        WpHookSpy::fire('rest_api_init');

        $this->assertNull($this->findRouteByUrl('/from-docblock'));
        $this->assertNotEmpty(WpHookSpy::actions('wp_ajax_AttributeApiService.docblockOnly'));
    }

    public function testRegisterEndpointsReturnsPublicMethods(): void
    {
        $service = new AttributeApiService();
        $methods = $service->registerEndpoints();

        $this->assertContains('ping', $methods);
        $this->assertContains('adminOnly', $methods);
        $this->assertNotContains('registerEndpoints', $methods);
    }

    /** @return array{namespace: string, route: string, args: array}|null */
    private function findRoute(string $namespace, string $route): ?array
    {
        foreach (WpHookSpy::restRoutes() as $registered) {
            if ($registered['namespace'] === $namespace && $registered['route'] === $route) {
                return $registered;
            }
        }

        return null;
    }

    /** @return array{namespace: string, route: string, args: array}|null */
    private function findRouteByUrl(string $route): ?array
    {
        foreach (WpHookSpy::restRoutes() as $registered) {
            if ($registered['route'] === $route) {
                return $registered;
            }
        }

        return null;
    }
}
