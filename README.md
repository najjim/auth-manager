# auth-manager
Manages Lumen Auth0 integratation in micro services.

## Installation
Via composer
```
$ composer require carsguide/auth-manager
```

## Environment settings .env file
```
AUTH0_AUDIENCE=
AUTH0_OAUTH_URL=
AUTH0_DOMAIN=
AUTH0_JWT_CLIENTID=
AUTH0_JWT_CLIENTSECRET=
AUTH0_ALGORITHM=
```

| Value         | What it is    |
| ------------- |-------------|
| AUTH0_AUDIENCE  | Auth0 audience/identifier of the API micro service verifying the token |
| AUTH0_OAUTH_URL | Auth0 URL to query to get a token from (the tenant) |
| AUTH0_DOMAIN | Auth0 domain of tenant (used during token verifcation) |
| AUTH0_JWT_CLIENTID | Auth0 client ID of the micro service getting a token |
| AUTH0_JWT_CLIENTSECRET | Auth0 client secret of the micro service getting a token |
| AUTH0_ALGORITHM | Algorithm method, advise RS256 (default) |


### Registering middleware
To use token and scope validation register the middleware via routeMiddleware() in bootstrap/app.php

```php
$app->routeMiddleware([
    'auth' => Carsguide\Auth\Middlewares\Auth0Middleware::class,
]);
```

## Usage
### Generate JWT Token
```php
use Carsguide\Auth\AuthManager;
use GuzzleHttp\Client;

$auth = new AuthManager(new Client());
$auth->getToken();
```

Using `AuthManager` Facade:

```php
AuthManager::getToken()
```

### Validate JWT Token / Scope Access
Each token is validated via middleware.  You must call the middleware in routes or the controller to validate access.  The middleware requires a scope be defined, nothing can be global.

```php
$this->middleware('auth:listings:read');
```

Using routes file

```php
$router->get('admin/profile', ['middleware' => 'auth:listings:read', function () {
    //
}]);
```
