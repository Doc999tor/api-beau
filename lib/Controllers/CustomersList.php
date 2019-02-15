<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CustomersList extends Controller {
	public function index (Request $request, Response $response):Response {
		$path = 'customers-list';
		$static_prefix = str_repeat('../', substr_count($request->getUri()->getPath(), '/'));
		$base_path = $request->getUri()->getBasePath();

		return $this->view->render($response, $path . '.html', [
			'base_path' => $base_path,
			'prefix' => $static_prefix,
			"path" => $path,
		]);
	}

	public function getClients (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!empty($params['phone'])) {
			$number = $params['phone'];
			if (!preg_match('/^[\d()+\-*\/]+$/', $number)) { return $response->withStatus(400)->getBody()->write('phone has to be a correct phone number, got ' . $number); }
			$clients = [];
			for ($i=0, $limit = rand(0, 3); $i < $limit; $i++) {
				$clients []= $this->generateClient();
				$clients[$i]['phone'] = $number;
			}
			return $response->withJson($clients);
		} else {
			if (!isset($params['limit'])) {
				return $response->withStatus(400);
			}
			if (!isset($params['offset'])) {
				$params['offset'] = 0;
			}
			$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';
		}

		return $response->withJson(self::generateClients($params['limit'], $q));
	}

	public function deleteClients (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_correct = true; $msg = '';
		// if (!\DateTime::createFromFormat('Y-m-d H:i:s', $body['date'])) { $is_correct = false; $msg .= "date has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>"; }

		if ($is_correct) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $msg);
			return $response->withStatus(400);
		}
	}
	public static function generateClients($limit, $q = '') {
		$clients = [];

		for ($i=0; $i < $limit; $i++) {
			$clients []= self::generateClient($q);
		}

		return $clients;
	}
	protected static function generateClient($q = '', $id = 0) {
		$id = rand(0, 300);
		$client = [
			'id' => $id,
			"profile_image" => "{$id}.jpg",
			'name' => \Lib\Helpers\Utils::generatePhrase($q, 1, 2),
			'phone' => '0' . mt_rand(2, 99) . '-' . mt_rand(1000000, 9999999),
		];
		if (rand(0,5)) {
			$client["last_appointment"] = date("Y-m-d", rand(time() - 3600 * 24 * 90, time() + 3600 * 24 * 30)) . ' ' . str_pad(rand(9,20), 2, '0', STR_PAD_LEFT) . ':' . (rand(0,1) ? '30' : '00'); # 3 months back and 1 forth
		}
		return $client;
	}

	public function checkPhoneNumberExists (Request $request, Response $response, array $args):Response {
		$number = filter_var($args['number'], FILTER_SANITIZE_STRING);

		$body = $response->getBody();

		if (!preg_match('/^[\d()+\-*\/]+$/', $number)) {
			$body->write("the number - $number is incorrect");
			return $response->withStatus(400);
		}

		$body->write(time() % 9 ? 'true' : 'false');
		return $response;
	}

	public function getCount (Request $request, Response $response):Response {
		return $response->withHeader('X-Total-Count', rand(50, 150));
	}
}