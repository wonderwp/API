# WonderWp API Component

WonderWp package to declare custom WordPress REST / ajax endpoints.

## Documentation

- [API component](http://wonderwp.net/Framewok_components/Ajax/index.html)
- [API services](http://wonderwp.net/Creating_a_plugin/Services/Api_service.html)

## Declaring endpoints (PHP attributes)

```php
use WonderWp\Component\API\AbstractApiService;
use WonderWp\Component\API\Annotation\WPApiEndpoint;
use WonderWp\Component\API\Annotation\WPApiNamespace;

#[WPApiNamespace(namespace: 'wwp-example')]
class ExampleApiService extends AbstractApiService
{
    #[WPApiEndpoint(url: '/ping', args: ['methods' => 'GET'])]
    public function ping()
    {
        // ...
    }
}
```

Doctrine docblock annotations are no longer read.

## Testing

### Unit (no WordPress instance — CI)

From the package root (or any host that can resolve `vendor/autoload.php`):

```bash
composer install
composer test
```

These tests use WordPress function stubs / spies (`add_action`, `register_rest_route`). They do **not** need a database.

### Integration (real WordPress — any host project)

Requires a running WordPress where `wonderwp/api` is installed via Composer (Bedrock, classic, DDEV, …). Not tied to wonderwp-base.

```bash
# From the WordPress project root:
wp eval-file vendor/wonderwp/api/tests/Integration/smoke-endpoints.php

# With DDEV:
ddev wp eval-file vendor/wonderwp/api/tests/Integration/smoke-endpoints.php
```

The smoke script registers a fixture service with `#[WPApiNamespace]` / `#[WPApiEndpoint]`, fires `rest_api_init`, and asserts routes exist on `rest_get_server()`.
