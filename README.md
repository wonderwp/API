# WonderWp API Component

### Wordpress API Usage example :

Based on https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/

Supposed we want this endpoint `https://domain.test/wp-json/questions/v1/questions-by-user?page=1` the annotation would be :
- **namespace** : corresponding `questions`
- **version** : corresponding to `v1`, we can maintain different api version by incrementing the version and keeping the same endpoint url (default to `v1`)
- **url** : corresponding to `questions-by-user`, can include dynamic parameters according to wordpress documentation (example `/author/(?P<id>\d+)`)
- **args** : corresponding to third parameter of `register_rest_route` function : [doc](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/#arguments)

```php

 class QuestionApiService extends AbstractApiService
{
    /**
     * @WPApiEndpoint(
     *     namespace = "questions",
     *     version = "v1",
     *     url = "/questions-by-user",
     *     args = {
    *       "methods": "GET",
    *       "args": {
    *           "page": {
    *               "default": 1,
    *               "sanitize_callback": "convertToInt"
    *           }, 
    *           "limit": {
    *               "default": 1,
    *               "sanitize_callback": "convertToInt"
    *           }
    *       },
    *       "permission_callback": "canFetchQuestions"
    *     }
     * )
     */
    public function questionsByUser(WP_REST_Request $request)
    {
        $page = $request->get_param('page');
        
        /* Processing get questions */
        
        // To handle error we can return WP_Error instance
        return WP_Error('error_code', 'Error message', ['status' => 400]);
    
        // Or success response
        return new WP_REST_Response([
            'questions' => [],
        ]);
    }
    
    public function canFetchQuestions(WP_REST_Request $request) 
    {
        $user = wp_get_current_user();
        
        /* processing verification */
        
        return true;
    }
    
    public function convertToInt($param, $request, $key)
    {
        /* Processing sanitize method */
        return (int) $param;
    }
}
```

The namespace could be define globaly.
For example to create this endpoint `https://domain.test/wp-json/questions/v1/delete-question/1` 

```php
/**
 * @WPApiNamespace(
 *     namespace="questions"
 * )
 */
class QuestionApiService extends AbstractApiService
{
/**
     * @WPApiEndpoint(
     *     url = "/delete-question/(?P<id>\d+)",
     *     args = {
     *      "methods": "DELETE",
     *      "permission_callback": "canDeleteQuestion"
     *     }
     * )
     */
    public function deleteQuestion(WP_REST_Request $request)
    {
        /* Processing deletion */
    }
    
    public function canDeleteQuestion()
    {}
}
```

### Important notice

For `sanitize_callback`, `validate_callback` and `permission_callback`, we are looking for an instance method on the same class of the annotation.
In second time, we look for a global function.
