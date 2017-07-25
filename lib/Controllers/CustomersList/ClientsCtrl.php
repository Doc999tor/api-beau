<?php

namespace Lib\Controllers\CustomersList;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends CustomersListController {
	public function getClients (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!isset($params['limit'])) {
			return $response->withStatus(400);
		}
		if (!isset($params['offset'])) {
			$params['offset'] = 0;
		}
		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		return $response->withJson($this->generateClients($params['limit'], $q));
	}

	public function deleteClients (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		if (!isset($params['ids'])) {
			$response->getBody()->write('ids must be provided');
			return $response->withStatus(400);
		}
		if (count($params) !== 1) {
			$response->getBody()->write('ids must be a single field here');
			return $response->withStatus(400);
		}

		$ids = str_replace(',', '', $params['ids']);

		if (!is_numeric($ids)) {
			$response->getBody()->write('ids should be numbers');
			return $response->withStatus(400);
		}

		return $response->withStatus(204);
	}
	protected function generateClients($limit, $q) {
		$clients = [];

		for ($i=0; $i < $limit; $i++) {
			$clients []= $this->generateClient($q);
		}

		return $clients;
	}
	protected function generateClient($q = '', $id = 0) {
		$client = [
			'id' => rand(0, 30000),
			'name' => $this->generatePhrase($q, 1, 2),
			'phone' => '0' . mt_rand(2, 99) . '-' . mt_rand(1000000, 9999999),
			"last_appoinment" => date("Y-m-d H:i", mt_rand(time() - 3600 * 24 * 90, time() + 3600 * 24 * 90)), # 3 months back and forth
		];
		return $client;
	}
}