<?php
// https://api.bewebmaster.co.il/appointments?service_id=%2C%2C
namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ServicesCtrl extends Controller {
	public function getAll (Request $request, Response $response) {
		// $params = $request->getQueryParams();
		// $offset = isset($params['offset']) ? filter_var($params['offset'], FILTER_SANITIZE_NUMBER_INT) : 0;
		// $q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';
		$offset = 0; $q = '';

		$is_one_category = rand(0,1);
		$is_above_category_limit = rand(0,1);
		$LIMIT_CATEGORIES = 50;
		$LIMIT_NO_CATEGORIES = 10;

		$limit = $is_above_category_limit ? $LIMIT_NO_CATEGORIES : $LIMIT_CATEGORIES;

		$services = [];
		for ($i=0; $i < $limit; $i++) {
			$services []= self::generateService(mt_rand(2, 150), $q, $is_one_category);
		}
		$services[0]['id'] = 1;

		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($services);
	}

	public function getBI (Request $request, Response $response):Response {
		$BI_LIMIT = 6;

		$services = [];
		for ($i=0; $i < $BI_LIMIT; $i++) {
			$services []= self::generateService(mt_rand(0, 150));
		}

		return $response->withJson($services);
	}

	public function getService (Request $request, Response $response, array $args):Response {
		return $response->withJson(self::generateService(filter_var($args['service_id'], FILTER_SANITIZE_NUMBER_INT)));
	}

	public static function generateService($id, $q = '', $is_one_category) {
		$possible_categories = $is_one_category ? ['sole category'] : ['', 'Hair styling', 'Cosmetics', 'Pilling', 'Massage', 'Manicure'];
		$category_id = array_rand($possible_categories);

		return [
			"id" => $id,  // AvodaID
			"name" => \Lib\Helpers\Utils::generatePhrase($q, 1, 6), // Name
			"duration" => 15 * \Lib\Helpers\Utils::rand_with_average(1, 40, 4, 0.1), // minutes < 10*60, TimeTipul
			"price" => 50 * \Lib\Helpers\Utils::rand_with_average(2, 100, 10, 0.1), // float, PriceTipul
			"color" => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)), // int, Color
			"category" => [
				"id" => $category_id + 1, // smallint, SpecializationID. id is 1-based
				"name" => $possible_categories[$category_id] // Name
			],
			// "shortName" => \Lib\Helpers\Utils::generatePhrase($q, 1, 3, 2, 7), // ShortName
		];
	}
	public static function generateServiceCalendar ($id) {
		return [
			"id" => $id,  // AvodaID
			"name" => \Lib\Helpers\Utils::generatePhrase('', 1, 6), // Name
			"color" => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)), // int, Color
			'count' => rand(1, 3),
		];
	}

	public function add (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);
		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) {
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
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$name = filter_var($body['name'], FILTER_SANITIZE_STRING);
		$added = filter_var($body['added'], FILTER_SANITIZE_STRING);

		$is_correct = true; $msg = '';
		if (empty($name)) { $is_correct = false; $msg .= "name cannot be empty<br>"; }
		if (!\DateTime::createFromFormat('Y-m-d H:i:s', $added)) { $is_correct = false; $msg .= "added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>"; }

		if ($is_correct) {
			return $response->withStatus(201);
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
		$correct_body = ['name', 'duration', 'price', 'color', 'category_id'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff($correct_body, array_keys($body));
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg = implode(', ', $diff_keys) . ' argument should exist';
		}

		if (empty($body['name'])) { $is_correct = false; $msg .= 'name cannot be empty' . "<br>"; }
		if (isset($body['duration']) && !ctype_digit($body['duration'])) { $is_correct = false; $msg .= 'duration has to be a positive integer' . "<br>"; }
		if (isset($body['price']) && !is_numeric($body['price'])) { $is_correct = false; $msg .= 'price has to be a number' . "<br>"; }
		if (isset($body['color']) && !($body['color'][0] === '#' && $this->checkColorValue(substr($body['color'], 1)))) { $is_correct = false; $msg .= $body['color'] . ' color has to be a valid hex value' . "<br>"; }
		if (isset($body['category_id']) && !ctype_digit($body['category_id'])) { $is_correct = false; $msg .= 'category_id has to be an integer' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function checkColorValue($color) {
		$hex_color_parts = 3;
		return $hex_color_parts === count(array_filter(str_split($color, mb_strlen($color)/3), 'ctype_xdigit'));
	}
}