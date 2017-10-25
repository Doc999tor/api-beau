<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ProceduresCtrl extends Controller {
	public function getAllProcedures (Request $request, Response $response) {
		$DEFAULT_LIMIT = 20;

		$params = $request->getQueryParams();

		$rand = mt_rand(0, 10);
		$limit = $rand > 3 ? 12 : $DEFAULT_LIMIT;

		$offset = isset($params['offset']) ? filter_var($params['offset'], FILTER_SANITIZE_NUMBER_INT) : 0;
		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		$procedures = [];
		for ($i=0; $i < $limit; $i++) {
			$procedures []= $this->generateProcedure(mt_rand(0, 150), $q);
		}

		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($procedures);
	}

	public function getBIProcedures (Request $request, Response $response):Response {
		$BI_LIMIT = 6;

		$procedures = [];
		for ($i=0; $i < $BI_LIMIT; $i++) {
			$procedures []= $this->generateProcedure(mt_rand(0, 150));
		}

		return $response->withJson($procedures);
	}

	public function get (Request $request, Response $response, array $args):Response {
		return $response->withJson($this->generateProcedure(filter_var($args['procedure_id'], FILTER_SANITIZE_NUMBER_INT)));
	}

	protected function generateProcedure($id, $q = '') {
		$possible_categories = ['', 'עיצוב שיער', 'קוסמטיקה', 'טיפולי פנים', 'טיפולי גוף', 'מניקור פדיקור'];
		$category_id = mt_rand(1, 5);
		return [
			"id" => $id,  // AvodaID
			"name" => \Lib\Helpers\Utils::generatePhrase($q, 1, 6), // Name
			"duration" => 15 * \Lib\Helpers\Utils::rand_with_average(1, 40, 4, 0.1), // minutes < 10*60, TimeTipul
			"price" => 50 * \Lib\Helpers\Utils::rand_with_average(2, 100, 10, 0.1), // float, PriceTipul
			"color" => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)), // int, Color
			"category" => [
				"id" => $category_id, // smallint, SpecializationID
				"name" => $possible_categories[$category_id] // Name
			],
			// "shortName" => \Lib\Helpers\Utils::generatePhrase($q, 1, 3, 2, 7), // ShortName
		];
	}

	public function add (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkBodyCorrectness($body) {
		$correct_body = ['name', 'duration', 'price', 'color', 'category_id'];

		$is_correct = true;
		$msg = '';

		if (!empty(array_diff($correct_body, array_keys($body)))) {
			$is_correct = false;
			$msg = implode(', ', array_diff($correct_body, array_keys($body))) . ' argument should exist';
		}

		if (empty($body['name'])) { $is_correct = false; $msg .= 'name cannot be empty' . "<br>"; }
		if (isset($body['duration']) && !ctype_digit($body['duration'])) { $is_correct = false; $msg .= 'duration ahve to be an integer' . "<br>"; }
		if (isset($body['price']) && !is_numeric($body['price'])) { $is_correct = false; $msg .= 'price have to be a number' . "<br>"; }
		if (isset($body['color']) && !($body['color'][0] === '#' && $this->checkColorValue(substr($body['color'], 1)))) { $is_correct = false; $msg .= $body['color'] . ' color has to be a valid hex value' . "<br>"; }
		if (isset($body['category_id']) && !ctype_digit($body['category_id'])) { $is_correct = false; $msg .= 'category_id have to be an integer' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function checkColorValue($color) {
		$hex_color_parts = 3;
		return $hex_color_parts === count(array_filter(str_split($color, mb_strlen($color)/3), 'ctype_xdigit'));
	}
}