<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class DeptCtrl extends Controller {
	public function addDept (Request $request, Response $response) {
		$response = $response->withHeader('Access-Control-Allow-Origin', '*');
		$body = $request->getParsedBody();

		if (time() % 10 === 0) {
			return $response->withHeader('Retry-After', 120)->withStatus(503);
		} else if (
			isset($body['sum']) && is_numeric($body['sum']) && $body['sum'] > 0 &&
			isset($body['desc'])
		) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write('"sum" has to be a positive number, "desc" can be empty');
			return $response->withStatus(400);
		}
	}
	public function updateDept (Request $request, Response $response, array $args) {
		$response = $response->withHeader('Access-Control-Allow-Origin', '*');
		$body = $request->getParsedBody();

		if (time() % 10 === 0) {
			return $response->withHeader('Retry-After', 120)->withStatus(503);
		} else if (
			isset($body['sum']) && is_numeric($body['sum']) && $body['sum'] > 0 &&
			isset($body['desc'])
		) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write('"sum" has to be a positive number, "desc" can be empty');
			return $response->withStatus(400);
		}
	}
	public function deleteDept (Request $request, Response $response, array $args) {
		$response = $response->withHeader('Access-Control-Allow-Origin', '*');

		if (time() % 10 === 0) {
			return $response->withHeader('Retry-After', 120)->withStatus(503);
		} else if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}
}