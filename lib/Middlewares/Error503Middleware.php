<?php
namespace Lib\Middlewares;

use \Psr\Http\Message\ServerRequestInterface	as Request;
use \Psr\Http\Message\ResponseInterface			as Response;

class Error503Middleware {
  public function __invoke(Request $request, Response $response, callable $next) {
    	$route = $request->getAttribute('route');

 		if ($route && $route->getMethods()[0] === 'OPTIONS' || time() % 10) {
			return $next($request, $response);
 		} else {
 			return $response
				->withHeader('Retry-After', 120)
				->withStatus(503);
 		}
  }
}