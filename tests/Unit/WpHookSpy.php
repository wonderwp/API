<?php

declare(strict_types=1);

namespace WonderWp\Component\API\Tests\Unit;

final class WpHookSpy
{
    public static function reset(): void
    {
        $GLOBALS['wwp_test_actions'] = [];
        $GLOBALS['wwp_test_rest_routes'] = [];
        $GLOBALS['wwp_test_wp_die'] = [];
    }

    public static function fire(string $hook): void
    {
        foreach ($GLOBALS['wwp_test_actions'][$hook] ?? [] as $callback) {
            $callback();
        }
    }

    /** @return list<callable> */
    public static function actions(string $hook): array
    {
        return $GLOBALS['wwp_test_actions'][$hook] ?? [];
    }

    /** @return list<array{namespace: string, route: string, args: array}> */
    public static function restRoutes(): array
    {
        return $GLOBALS['wwp_test_rest_routes'] ?? [];
    }
}
