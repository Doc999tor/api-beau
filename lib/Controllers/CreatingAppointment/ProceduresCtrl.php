<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ProceduresCtrl extends Controller {
	public function getProceduresData (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!isset($params['limit'])) {
			return $response->withStatus(400);
		}
		if (!isset($params['offset'])) {
			$params['offset'] = 0;
		}
		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($this->generateProcedures($params['limit'], $q));
	}

	protected function generateProcedures($limit, $q) {
		$procedures = [];

		$procedures['bi'] = [];
		$procedures_bi_length = rand(1, 11);
		for ($i=0; $i < $procedures_bi_length; $i++) {
			$procedures['bi'] []= $this->generateProcedure($q);
		}

		$procedures['all'] = [];
		for ($i=0; $i < $limit; $i++) {
			$procedures['all'] []= $this->generateProcedure($q);
		}

		return $procedures;
	}

	protected function generateProcedure($q) {
		$client = [
			"id" => mt_rand(0, 30000),  // AvodaID
			"time" => 15 * $this->rand_with_average(1, 40, 4, 0.1), // minutes < 10*60, TimeTipul
			"price" => 50 * $this->rand_with_average(2, 100, 10, 0.1), // float, PriceTipul
			"categoryId" => mt_rand(0, 30000), // smallint, SpecializationID
			"color" => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)), // int, Color
			"shortName" => $this->generatePhrase($q, 1, 3, 1, 5), // ShortName
			"name" => $this->generatePhrase($q, 1, 6, 2, 10) // Name
		];
		return $client;
	}
}