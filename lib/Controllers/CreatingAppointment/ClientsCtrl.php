<?php

namespace Lib\Controllers\CreatingAppointment;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends CreatingAppointmentController {
	public function getClients (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!isset($params['limit'])) {
			$response->getBody()->write('limit field is missing');
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

		$client = $this->generateClient('');
		$client['id'] = $id;

		return $response->withJson($client);
	}
	protected function generateClients($limit, $q) {
		$clients = [];

		// $clients['bi'] = [];
		// $clients_bi_length = rand(1, 11);
		// for ($i=0; $i < $clients_bi_length; $i++) {
		// 	$clients['bi'] []= $this->generateClient($q);
		// }

		if (mb_strlen($q) === 3) {
			if (mt_rand(0,10) > 4) {
				$limit = round(mt_rand(0,5));
			} else {
				return $clients;
			}
		} else if (mb_strlen($q) === 4) {
			if (mt_rand(0,10) > 5) {
				$limit = round(mt_rand(0,4));
			} else {
				return $clients;
			}
		} else if (mb_strlen($q) === 5) {
			if (mt_rand(0,10) > 6) {
				$limit = round(mt_rand(0,3));
			} else {
				return $clients;
			}
		} else if (mb_strlen($q) > 5) {
			return $clients;
		}

		if (mt_rand(0,10) > 8) {
			$limit = floor(mt_rand(0, $limit));
		}

		// $clients['all'] = [];
		for ($i=0; $i < $limit; $i++) {
			$clients []= $this->generateClient($q);
			// $clients['all'] []= $this->generateClient($q);
		}

		return $clients;
	}
	protected function generateClient($q = '', $id = 0, $is_full = false) {
		$client = [
			'id' => rand(0, 30000),
			'name' => $this->generatePhrase($q, 1, 2),
		];

		if (mt_rand(0,10) < 9) {
			$client['phone'] = '0' . mt_rand(2, 99) . '-' . mt_rand(1000000, 9999999);
		}
		if (mt_rand(0,10) < 9) {
			$client["last_appointment"] = date("Y-m-d H:i", mt_rand(time() - 3600 * 24 * 30, time())); # 1 month back,
		}
		if (mt_rand(0,10) < 9) {
			$client["next_appointment"] = date("Y-m-d H:i", mt_rand(time(), time() + 3600 * 24 * 30)); # 1 month forth,
		}
		if ($is_full) {
			$client['id'] = $id;
			$client['phone'] = '0' . mt_rand(2, 99) . '-' . mt_rand(1000000, 9999999);
		}
		return $client;
	}
}