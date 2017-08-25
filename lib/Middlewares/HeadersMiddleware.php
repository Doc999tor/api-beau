<?php
namespace Lib\Middlewares;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface		 as Response;

class HeadersMiddleware {
    public function __invoke(Request $request, Response $response, callable $next) {
        $response = $next($request, $response);

        if ($request->getMethod() === 'GET') {
			$response = $response->withHeader('X-Robots-Tag', 'noindex, nofollow');
        }
		return $response;
    }
}