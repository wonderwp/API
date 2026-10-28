<?php
/**
 * Integration smoke: register attribute-based endpoints against a real WordPress.
 *
 * Run from ANY WordPress project where wonderwp/api is installed, e.g.:
 *
 *   wp eval-file vendor/wonderwp/api/tests/Integration/smoke-endpoints.php
 *   ddev wp eval-file vendor/wonderwp/api/tests/Integration/smoke-endpoints.php
 */

use WonderWp\Component\API\Tests\Fixtures\AttributeApiService;

if (!defined('ABSPATH')) {
    fwrite(STDERR, "WordPress is not loaded. Run via WP-CLI from a project that requires wonderwp/api.\n");
    exit(1);
}

$autoloadCandidates = [
    dirname(__DIR__, 4) . '/autoload.php',        // {project}/vendor/autoload.php
    dirname(__DIR__, 2) . '/vendor/autoload.php', // package-local vendor/
];
foreach ($autoloadCandidates as $autoload) {
    if (is_file($autoload)) {
        require_once $autoload;
        break;
    }
}

// Fixtures are autoload-dev — always load explicitly for host WP-CLI runs.
require_once dirname(__DIR__) . '/Fixtures/AttributeApiService.php';

if (!class_exists(\WonderWp\Component\API\AbstractApiService::class)) {
    fwrite(STDERR, "wonderwp/api is not autoloadable from this WordPress project.\n");
    exit(1);
}

$service = new AttributeApiService();
$service->registerEndpoints();
do_action('rest_api_init');

$routes = rest_get_server()->get_routes();
$required = [
    '/wwp-test/v1/ping',
    '/wwp-override/v2/override',
];

$failed = 0;
foreach ($required as $route) {
    if (!isset($routes[$route])) {
        fwrite(STDERR, "FAIL missing REST route: {$route}\n");
        $failed++;
        continue;
    }
    fwrite(STDOUT, "PASS route exists: {$route}\n");
}

if (isset($routes['/wwp-test/v1/from-docblock'])) {
    fwrite(STDERR, "FAIL docblock-only route should not be registered\n");
    $failed++;
} else {
    fwrite(STDOUT, "PASS docblock-only route not registered\n");
}

if ($failed > 0) {
    exit(1);
}

fwrite(STDOUT, "OK wonderwp/api Integration smoke-endpoints\n");
