# WonderWp API Component

### Wordpress API Usage example :

Based on https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/

Supposed we want this endpoint `https://domain.test/wp-json/questions/v1/questions-by-user?page=1` the annotation would be :
- **namespace** : corresponding `questions`
- **version** : corresponding to `v1`, we can maintain different api version by incrementing the version and keeping the same endpoint url
- **url** : corresponding to `questions-by-user`, can include dynamic parameters according to wordpress documentation (example `/author/(?P<id>\d+)`)
- **method** : corresponding to HTTP verb `GET`, `POST` etc
- **args** : corresponding to (example `?page=1`) request arguments : [doc](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/#arguments)

```php
/**
 * @WPApiEndpoint(
 *     namespace = "questions",
 *     version = "v1",
 *     url = "/questions-by-user",
 *     method = "GET",
 *     args = {"page": {"default": 1}, "limit": {"default": 1}}
 * )
 */
public function questionsByUser(WP_REST_Request $request)
{
    $userId = $request->get_param('userId');
    
    /* ... */

    return new WP_REST_Response([
        'questions' => [],
    ]);
}
```
