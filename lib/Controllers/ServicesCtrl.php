<?php
// https://api.bewebmaster.co.il/appointments?service_id=%2C%2C
namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Lib\Helpers\Utils;

class ServicesCtrl extends Controller {
	public function getAll (Request $request, Response $response) {
		// $params = $request->getQueryParams();
		// $offset = isset($params['offset']) ? filter_var($params['offset'], FILTER_SANITIZE_NUMBER_INT) : 0;
		// $q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';
		$offset = 0; $q = '';

		$is_one_category = rand(0,1);
		$LIMIT_CATEGORIES = 50;
		$LIMIT_NO_CATEGORIES = 10;
		$NO_SERVICES = 0;

		$limit = 0;
		switch (rand(1,3)) {
			case 1: $limit = $NO_SERVICES; break;
			case 2: $limit = $LIMIT_NO_CATEGORIES; break;
			default: $limit = $LIMIT_CATEGORIES; break;
		}

		$services = [];
		for ($i=0, $end = rand(1, $limit); $i <= $end; $i++) {
			$services []= self::generateService(mt_rand(2, 150), $q, $is_one_category);
		}

		if ($limit > 0) { $services[0]['id'] = 1; }

		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($services);
	}
	public function getAllRT (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!empty($params['sorting_criteria'])) {
			$services = [];
			$services_count = rand(0, 10);
			for ($k=1; $k <= $services_count; $k++) {
				$service = self::generateServiceRT(++$service_id);
				$service['order'] = $k;
				$service['category_id'] = rand(1, 5);
				$service['category_name'] = Utils::generateWord(5);
				$services []= $service;
			}

			$sorting_criteria = $params['sorting_criteria'];
			$multiplier = 1;
			switch ($sorting_criteria) {
				case 'total_income':
					$multiplier = round(rand(0, 100000) / 100, 2);
					break;
				case 'appointment_count':
					$multiplier = 1;
					break;
			}

			foreach ($services as &$service) {
				$service[$sorting_criteria] = round(rand(0, 100) * $multiplier, 2);
			}
			$order_directions = ['desc' => -1, 'asc' => 1];
			usort($services, function($a, $b) use ($sorting_criteria, $order_directions, $params) {
				return ($a[$sorting_criteria] <=> $b[$sorting_criteria]) * $order_directions[$params['order']];
			});

			return $response->withJson($services);
		}

		$categories = [];

		$categories_count = rand(0, 3);
		$service_id = 0;
		for ($i=1; $i <= $categories_count; $i++) {
			$services = [];
			$services_count = rand(0, 5);
			for ($k=1; $k <= $services_count; $k++) {
				$service = self::generateServiceRT(++$service_id);
				$service['order'] = $k;
				if (rand(0, 3) === 0) {
					$service['is_price_hidden_online_booking'] = true;
				}
				if (rand(0, 3) === 0) {
					$service['is_duration_hidden_online_booking'] = true;
				}
				$services []= $service;
			}

			$category = [
				"category_id" => $i,
				"category_name" => Utils::generateWord(5),
				"services" => $services,
			];
			$categories []= $category;
		}

		return $response->withJson($categories);
	}

	public function getBI (Request $request, Response $response):Response {
		$BI_LIMIT = 6;

		$services = [];
		for ($i=0; $i < $BI_LIMIT; $i++) {
			$services []= self::generateService(mt_rand(0, 150));
			$services[$i]['id'] = $i;
		}

		return $response->withJson($services);
	}

	public function getService (Request $request, Response $response, array $args):Response {
		return $response->withJson(self::generateService(filter_var($args['service_id'], FILTER_SANITIZE_NUMBER_INT)));
	}

	public static function generateServiceRT(int $id): array {
		$colors = ['#5de0c8', '#5dd6ee', '#80c4ff', '#9da5e3', '#bb81ee', '#ff80ab', '#ffacac', '#ffda12'];
		return [
			"service_id" => $id,  // AvodaID
			"service_name" => Utils::generatePhrase('', 1, 6), // Name
			"public_name" => Utils::generatePhrase('', 1, 6), // Name
			"duration" => 15 * Utils::rand_with_average(1, 40, 4, 0.1), // minutes < 10*60, TimeTipul
			"price" => 50 * Utils::rand_with_average(2, 100, 10, 0.1), // float, PriceTipul
			"color" => $colors[array_rand($colors)],
			'is_open_online' => (bool) rand(0, 1),
			'is_group' => (bool) rand(0, 2),
			'group_amount' => ((bool) rand(0, 2))
				? rand(3, 5)
				: 1,
		];
	}
	public static function generateService($id, $q = '', $is_one_category = false) {
		$possible_categories = $is_one_category ? ['sole category'] : ['Hair styling', 'Cosmetics', 'Pilling', 'Massage', 'Manicure'];
		$category_id = array_rand($possible_categories);

		return [
			"id" => $id,  // AvodaID
			"name" => Utils::generatePhrase($q, 1, 6), // Name
			"duration" => 15 * Utils::rand_with_average(1, 40, 4, 0.1), // minutes < 10*60, TimeTipul
			"price" => Utils::rand_with_average(50, 1000, 300, 0.1) / 10, // float, PriceTipul
			"color" => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)), // int, Color
			"category" => [
				"id" => $category_id + 1, // smallint, SpecializationID. id is 1-based
				"name" => $possible_categories[$category_id] // Name
			],
			// "shortName" => Utils::generatePhrase($q, 1, 3, 2, 7), // ShortName
		];
	}
	public static function generateServiceCalendar ($id) {
		$service = [
			"id" => $id,  // AvodaID
			"name" => Utils::generatePhrase('', 1, 6), // Name
			"public_name" => Utils::generatePhrase('', 1, 6), // Name
			"duration" => 15 * Utils::rand_with_average(1, 40, 4, 0.1), // minutes < 10*60, TimeTipul
			"color" => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)), // int, Color
			'count' => rand(1, 3),
		];
		if (rand(0, 3) === 0) {
			$service['is_price_hidden_online_booking'] = true;
		}
		if (rand(0, 3) === 0) {
			$service['is_duration_hidden_online_booking'] = true;
		}
		return $service;
	}

	public function add (Request $request, Response $response):Response {
		$body = json_decode($request->getBody()->getContents(), true);
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);
		if (!isset($body['added']) || empty(new \DateTime($body['added']))) {
			$is_body_correct['is_correct'] = false;
			$is_body_correct['msg'] .= ' added has exist and to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54 <br>';
		}

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	public function update (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function singleUpdate (Request $request, Response $response):Response {
		$body = json_decode($request->getBody()->getContents(), true);
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkSingleDetailCorrectness($body);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	public function delete (Request $request, Response $response):Response {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}

	public function addCategory (Request $request, Response $response):Response {
		$body = json_decode($request->getBody()->getContents(), true);
		$body = is_array($body) ? $body : [];

		$name = filter_var($body['name'], FILTER_SANITIZE_STRING);
		$added = filter_var($body['added'], FILTER_SANITIZE_STRING);

		$is_correct = true; $msg = '';
		if (empty($name)) { $is_correct = false; $msg .= "name cannot be empty<br>"; }
		// if (!\DateTime::createFromFormat('Y-m-d H:i:s', $added)) { $is_correct = false; $msg .= "added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>"; }
		if (empty(new \DateTime($added))) { $is_correct = false; $msg .= "added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>"; }

		if ($is_correct) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $msg);
			return $response->withStatus(400);
		}
	}

	public function renameCategory (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$name = filter_var($body['name'], FILTER_SANITIZE_STRING);

		$is_correct = true; $msg = '';
		if (empty($name)) { $is_correct = false; $msg .= "name cannot be empty<br>"; }

		if ($is_correct) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $msg);
			return $response->withStatus(400);
		}
	}
	public function deleteCategory (Request $request, Response $response):Response {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}
	private function checkBodyCorrectness($body) {
		$correct_body = ['name', 'public_name', 'duration', 'price', 'color', 'category_id'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff($correct_body, array_keys($body));
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg = implode(', ', $diff_keys) . ' argument should exist';
		}

		if (empty($body['name'])) { $is_correct = false; $msg .= 'name cannot be empty' . "<br>"; }
		if (isset($body['duration']) && !ctype_digit((string) $body['duration'])) { $is_correct = false; $msg .= 'duration has to be a positive integer' . "<br>"; }
		if (isset($body['price']) && !is_numeric($body['price'])) { $is_correct = false; $msg .= 'price has to be a number' . "<br>"; }
		if (isset($body['color']) && !($body['color'][0] === '#' && $this->checkColorValue(substr($body['color'], 1)))) { $is_correct = false; $msg .= $body['color'] . ' color has to be a valid hex value' . "<br>"; }
		if (isset($body['category_id']) && !ctype_digit((string) $body['category_id'])) { $is_correct = false; $msg .= 'category_id has to be an integer' . "<br>"; }

		if (isset($body['is_open_online']) && !is_bool($body['is_open_online'])) { $is_correct = false; $msg .= 'is_open_online has to be a number' . "<br>"; }
		if (isset($body['is_group']) && !is_bool($body['is_group'])) { $is_correct = false; $msg .= 'is_group has to be a number' . "<br>"; }
		if (isset($body['group_amount']) && !ctype_digit((string) $body['group_amount'])) { $is_correct = false; $msg .= 'group_amount has to be an integer' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function checkSingleDetailCorrectness($body) {
		$correct_body = ['is_open_online', 'order'];

		$is_correct = true;
		$msg = '';

		if (isset($body['is_open_online']) && !is_bool($body['is_open_online'])) { $is_correct = false; $msg .= 'is_open_online has to be a number' . "<br>"; }
		if (isset($body['order']) && !is_int($body['order'])) { $is_correct = false; $msg .= 'order has to be an integer' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function checkColorValue($color) {
		$hex_color_parts = 3;
		return $hex_color_parts === count(array_filter(str_split($color, mb_strlen($color)/3), 'ctype_xdigit'));
	}
}
