<?php

namespace Lib\Controllers;

use \Lib\Controllers\Controller;
use \Lib\Controllers\ServicesCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Lib\Helpers\Utils;

class WorkersCtrl extends Controller {
	public function getAllWorkersData (Request $request, Response $response, array $args):Response {
		$workers = [];
		for ($i=1; $i < rand(1, 6); ++$i) {
			$workers []= $this->generateWorker($i);
		}

		return $response->withJson($workers);
	}
	public function getData (Request $request, Response $response, array $args):Response {
		return $response->withJson($this->generateWorker(filter_var($args['worker_id'], FILTER_SANITIZE_NUMBER_INT)));
	}

	private function generateWorker(int $id) {
		$faker = \Faker\Factory::create();

		$worker = [
			'id' => $id,
			'name' => $faker->firstName,
		];

		$weekend = rand(0,6);
		$short_day = rand(0,6);
		for ($i=0; $i < 7; $i++) {
			if ($i === $weekend) { continue; }
			$worker['day_' . $i] = [
				'start' => '10:00',
				'end' => '18:00',
			];
			if ($i === $short_day) {
				$worker['day_' . $i]['end'] = '14:00';
			}
		}

		return $worker;
	}
}
