<?php

namespace Lib\Controllers;

use \Lib\Controllers\Controller;
use \Lib\Controllers\ServicesCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Lib\Helpers\Utils;

class WorkersCtrl extends Controller {
	public function getData (Request $request, Response $response, array $args):Response {
		$data = [
			'id' => filter_var($args['worker_id'], FILTER_SANITIZE_NUMBER_INT),
			'name' => Utils::generatePhrase('', 1, 2),
		];

		$weekend = rand(0,6);
		$short_day = rand(0,6);
		for ($i=0; $i < 7; $i++) {
			if ($i === $weekend) {continue;}
			$data['day_' . $i] = [
				'start' => '10:00',
				'end' => '18:00',
			];
			if ($i === $short_day) {
				$data['day_' . $i]['end'] = '14:00';
			}
		}

		return $response->withJson($data, 200, JSON_PRETTY_PRINT);
	}
}