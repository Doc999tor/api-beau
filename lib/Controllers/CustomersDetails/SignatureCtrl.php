<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SignatureCtrl extends Controller {
	public function addSignature (Request $request, Response $response) {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		if (empty($body)) {
			$response->getBody()->write('request body can\'t be empty');
			return $response->withStatus(400);
		} else if (count($body) > 1) {
			$response->getBody()->write('request body should include only sign field');
			return $response->withStatus(400);
		} else if (!isset($body['sign'])) {
			$response->getBody()->write('field "sign" doesn\'t exists');
			return $response->withStatus(400);
		} else if (strpos($body['sign'], 'data:image/jpeg;base64,') !== 0) {
			$response->getBody()->write('base64 image isn\'t encoded properly');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}
	public function deleteSignature (Request $request, Response $response) {
		if ($request->getBody()->getSize()) {
			$response->getBody()->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}
}