<?php

namespace Lib\Controllers\CustomersDetails;

use \Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends Controller {
	public function getClients (Request $request, Response $response) {
		$response->getBody()->write('clients');
	}
	public function setVip (Request $request, Response $response) {
		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		$body = $request->getParsedBody();

		if (isset($body['vip']) && ($body['vip'] === 'true' || $body['vip'] === 'false')) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('missing or incorrect vip request body param');
			return $response->withStatus(400);
		}
	}
}