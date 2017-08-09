<?php

namespace Lib\Middlewares;

use \Psr\Http\Message\ServerRequestInterface	as Request;
use \Psr\Http\Message\ResponseInterface				as Response;

class CorsMiddleware {
	public function __invoke(Request $request, Response $response, callable $next) {
	    $response = $next($request, $response);

	    return $response
			->withHeader('Access-Control-Allow-Methods', 'POST,PUT,PATCH,DELETE')
			->withHeader('Access-Control-Allow-Origin', '*');
			// ->withHeader('Access-Control-Expose-Headers', 'Retry-After');
			// ->withHeader('Access-Control-Allow-Headers', 'Retry-After');
	}
}