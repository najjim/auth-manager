<?php

namespace Carsguide\Auth\Middlewares;

use Auth0\SDK\Exception\CoreException;
use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\JWTVerifier;
use Carsguide\Auth\Handlers\CacheHandler;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AddJwtFieldsMiddleware
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure $next
     * @param  string $scope
     * @return mixed
     */
    public function handle($request, Closure $next, $fields)
    {
        $this->request = $request;

        //verify and decode token
        try {
            $this->verifyAndDecodeToken($this->request->bearerToken());
        } catch (InvalidTokenException $e) {
            Log::info('Invalid token');
            return $this->json('Invalid token', 401);
        } catch (CoreException $e) {
            Log::warning('Auth0 Core Exception', ['exceptionMessage' => $e->getMessage()]);
            return $this->json($e->getMessage(), 401);
        }

        $this->explodeFields($fields);

        $this->addFieldsToRequest();

        //all ok, proceed
        return $next($this->request);
    }

    /**
     * verify and decode the token
     *
     * @param string $token
     * @return void
     */
    public function verifyAndDecodeToken($token)
    {
        $verifier = new JWTVerifier([
            'supported_algs' => [env('AUTH0_ALGORITHM', 'RS256')],
            'valid_audiences' => [env('AUTH0_AUDIENCE', false)],
            'authorized_iss' => [env('AUTH0_DOMAIN', false)],
            'cache' => new CacheHandler(),
        ]);

        $this->decodedToken = $verifier->verifyAndDecode($token);
    }

    /**
     * Explode fields
     *
     * @param $fields
     * @return void
     */
    public function explodeFields($fields)
    {
        $this->fields = explode(':', $fields);
    }

    /**
     * Explode fields
     *
     * @return void
     */
    public function addFieldsToRequest()
    {
        foreach ($this->fields as $field) {
            if (!empty($this->decodedToken->$field)) {
                $this->request->merge([$field => $this->decodedToken->$field]);
            }
        }
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param  string|array  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return \Illuminate\Http\JsonResponse;
     */
    protected function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse($data, $status, $headers, $options);
    }
}
