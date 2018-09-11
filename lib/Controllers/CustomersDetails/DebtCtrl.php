<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class DebtCtrl extends Controller {
	public function addDebt (Request $request, Response $response) {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		if ($this->checkCorrectness($body)) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write('"sum" has to be a positive number, "desc" can be empty');
			return $response->withStatus(400);
		}
	}
	public function updateDebt (Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		if ($this->checkCorrectness($body)) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write('"sum" has to be a positive number, "desc" can be empty');
			return $response->withStatus(400);
		}
	}
	public function deleteDebt (Request $request, Response $response, array $args) {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}

	private function checkCorrectness(array $body):bool {
		return isset($body['sum'])
			&& is_numeric($body['sum'])
			&& $body['sum'] > 0
			&& isset($body['desc']);
	}
}