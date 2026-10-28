<?php

declare(strict_types=1);

namespace {
    if (!function_exists('add_action')) {
        function add_action($hook, $callback, $priority = 10, $accepted_args = 1): void
        {
            $GLOBALS['wwp_test_actions'][$hook][] = $callback;
        }
    }

    if (!function_exists('register_rest_route')) {
        function register_rest_route($namespace, $route, $args = [], $override = false): bool
        {
            $GLOBALS['wwp_test_rest_routes'][] = [
                'namespace' => $namespace,
                'route' => $route,
                'args' => $args,
            ];

            return true;
        }
    }

    if (!function_exists('wp_die')) {
        function wp_die($message = ''): void
        {
            $GLOBALS['wwp_test_wp_die'][] = $message;
        }
    }

    if (!function_exists('__return_true')) {
        function __return_true(): bool
        {
            return true;
        }
    }
}
