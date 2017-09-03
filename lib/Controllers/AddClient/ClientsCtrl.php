<?php

namespace Lib\Controllers\AddClient;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends AddClientController {
	public function getClients (Request $request, Response $response) {
		$params = $request->getQueryParams();

		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		return $response->withJson($this->generateClients(50, $q));
	}

	public function getClient (Request $request, Response $response, $args) {
		$params = $request->getQueryParams();

		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if (!(int)$id) {
			return $response->withStatus(400);
		}

		return $response->withJson($this->generateClient('', $id, true));
	}
	protected function generateClients($limit, $q) {
		$clients = [];

		// Reducing $limit as length of $q rises
		switch (mb_strlen($q)) {
			case 3: $limit = (time()%10 > 4) ? round(mt_rand(0, $limit)) : 0;
				break;
			case 4: $limit = (time()%10 > 5) ? round(mt_rand(0, $limit)) : 0;
				break;
			case 5: $limit = (time()%10 > 6) ? round(mt_rand(0, $limit)) : 0;
				break;
			default: $limit = 0;
				break;
		}

		for ($i=0; $i < $limit; $i++) {
			$clients []= $this->generateClient($q);
		}

		return $clients;
	}
	protected function generateClient($q = '', $id = 0, $is_full = false) {
		return [
			'id' => rand(0, 30000),
			'name' => $this->generatePhrase($q, 1, 2),
		];
	}
}