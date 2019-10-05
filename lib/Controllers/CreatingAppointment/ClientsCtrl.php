<?php
namespace Lib\Controllers\CreatingAppointment;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Lib\Controllers\Controller;
use Lib\Controllers\CustomersList;
use \Lib\Helpers\Utils;

class ClientsCtrl extends Controller {
	private $addressHandler;

	public function index (Request $request, Response $response):Response {
		$path = 'creating-appointment';
		$static_prefix = str_repeat('../', substr_count($request->getUri()->getPath(), '/'));
		$base_path = $request->getUri()->getBasePath();

		return $this->view->render($response, $path . '.html', [
			'base_path' => $base_path,
			'prefix' => $static_prefix,
			"path" => $path,
		]);
	}

	public function getClients (Request $request, Response $response):Response {
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
	public function getBIClients (Request $request, Response $response):Response {
		$bi_limit = 6;
		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($this->generateClients($bi_limit, ''));
	}

	public function getClient (Request $request, Response $response, $args):Response {
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
		$client = CustomersList::generateClient($q, $id);

		if ($is_full) {
			$client['id'] = $id;
			$client['phone'] = '0' . rand(2, 99) . '-' . rand(1000000, 9999999);
		}
		return $client;
	}
}