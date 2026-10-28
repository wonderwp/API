<?php

declare(strict_types=1);

$candidates = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(__DIR__, 3) . '/autoload.php',
];

$autoload = null;
foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $autoload = $candidate;
        break;
    }
}

if ($autoload === null) {
    fwrite(STDERR, "Unable to locate Composer autoload.php for wonderwp/api unit tests.\n");
    exit(1);
}

// PluginSkeleton is not a direct dependency of api/service; stub it for isolated runs.
require_once __DIR__ . '/stub-plugin-skeleton.php';
require_once $autoload;
require_once __DIR__ . '/stub-wordpress.php';

// Host dump-autoload usually omits package autoload-dev — register tests PSR-4.
spl_autoload_register(static function (string $class): void {
    $prefix = 'WonderWp\\Component\\API\\Tests\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$GLOBALS['wwp_test_actions'] = [];
$GLOBALS['wwp_test_rest_routes'] = [];
