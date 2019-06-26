<?php

namespace Lib\Middlewares;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface		 as Response;

class RandomReturn422 {
	public function __invoke(Request $request, Response $response, callable $next) {
		$response = $next($request, $response);

		if (!(rand(1,10) % 10)) {
			$response = $response->withStatus(422);
		}

		return $response;
	}
}