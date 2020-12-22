<?php

namespace Lib\Middlewares;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface		 as Response;

class PostReturnIDMiddleware {
	public function __invoke(Request $request, Response $response, callable $next) {
		$response = $next($request, $response);

		if ($response->getStatusCode() === 201 || $response->getStatusCode() === 409) {
			$body = $response->getBody()->write(self::getRandomID());
		}

		return $response;
	}

	public static function getRandomID() {
		return mt_rand(0, 150);
	}
}
