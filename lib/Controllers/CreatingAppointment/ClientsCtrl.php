<?php

namespace Lib\Controllers\CreatingAppointment;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends Controller {
	public function getClients (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!isset($params['limit'])) {
			return $response->withStatus(400);
		}
		if (!isset($params['offset'])) {
			$params['offset'] = 0;
		}
		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($this->generateClients($params['limit'], $q));
	}

	public function getClient (Request $request, Response $response, $args) {
		$params = $request->getQueryParams();

		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if (!(int)$id) {
			return $response->withStatus(400);
		}

		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($this->generateClient('', $id, true));
	}
	protected function generateClients($limit, $q) {
		$clients = [];

		$clients['bi'] = [];
		$clients_bi_length = rand(1, 11);
		for ($i=0; $i < $clients_bi_length; $i++) {
			$clients['bi'] []= $this->generateClient($q);
		}

		$clients['all'] = [];
		for ($i=0; $i < $limit; $i++) {
			$clients['all'] []= $this->generateClient($q);
		}

		return $clients;
	}
	protected function generateClient($q = '', $id = 0, $is_full = false) {
		$client = [
			'customerId' => rand(0, 30000),
			'fullName' => $this->generatePhrase($q, 1, 2),
			'phone' => '0' . mt_rand(2, 99) . '-' . mt_rand(1000000, 9999999)
		];
		if ($is_full) {
			$client['customerId'] = $id;
			$client['phone'] = '0' . mt_rand(2, 99) . '-' . mt_rand(1000000, 9999999);
		}
		return $client;
	}
}