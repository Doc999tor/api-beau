<?php

namespace Lib\Controllers\CreatingAppointment;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ProceduresCtrl extends CreatingAppointmentController {
	public function getAllProcedures (Request $request, Response $response) {
		$DEFAULT_LIMIT = 20;

		$params = $request->getQueryParams();
		if (!isset($params['limit'])) {
			$params['limit'] = $DEFAULT_LIMIT;
		}
		if (!isset($params['offset'])) {
			$params['offset'] = 0;
		}
		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		$procedures = [];
		for ($i=0; $i < $params['limit']; $i++) {
			$procedures []= $this->generateProcedure($q);
		}

		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($procedures);
	}

	public function getBIProcedures (Request $request, Response $response):Response {
		$DEFAULT_LIMIT = 6;

		$procedures = [];
		for ($i=0; $i < $DEFAULT_LIMIT; $i++) {
			$procedures []= $this->generateProcedure();
		}

		return $response->withJson($procedures);
	}

	protected function generateProcedure($q = '') {
		return [
			"id" => mt_rand(0, 30000),  // AvodaID
			"time" => 15 * $this->rand_with_average(1, 40, 4, 0.1), // minutes < 10*60, TimeTipul
			"price" => 50 * $this->rand_with_average(2, 100, 10, 0.1), // float, PriceTipul
			"categoryId" => mt_rand(0, 30000), // smallint, SpecializationID
			"color" => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)), // int, Color
			"shortName" => $this->generatePhrase($q, 1, 3, 1, 5), // ShortName
			"name" => $this->generatePhrase($q, 1, 6, 2, 10) // Name
		];
	}
}