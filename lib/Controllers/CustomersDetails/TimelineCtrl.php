<?php

namespace Lib\Controllers\CustomersDetails;

use \Lib\Helpers\Utils as Utils;
use Lib\Controllers\Controller as Controller;
use Lib\Controllers\ProceduresCtrl as ProceduresCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class TimelineCtrl extends Controller {
	public function getAppoinments (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		$start_date = filter_var($params['start'], FILTER_SANITIZE_STRING);
		$end_date = filter_var($params['end'], FILTER_SANITIZE_STRING);

		$period = new \DatePeriod(new \DateTime($start_date), new \DateInterval('P1D'), new \DateTime($end_date));
		// var_dump($period);
		// $period->rewind();
		// $dates = iterator_to_array($period);

		return $response->withJson($this->generateAppointment($period->start));
	}
	public function getGallery (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		return $response->getBody()->write('response body');
	}
	public function getDepts (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		return $response->getBody()->write('response body');
	}
	public function getNotes (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		return $response->getBody()->write('response body');
	}
	public function getSms (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		return $response->getBody()->write('response body');
	}

	private function generateAppointment(\DateTime $date) {
		$procedures_count = rand(1, 5);
		$is_deleted = !(rand(1, 5) % 5);

		$added_date = clone $date;
		$added_date->sub(new \DateInterval('P' . rand(3, 10) . 'D'));

		$appoinment = [
			"id" => rand(1, 1000),
			"date" => $date->format('Y-m-d'),
			"added_date" => $added_date->format('Y-m-d'),
			"worker_id" => rand(1, 5),
			"worker_name" => Utils::generatePhrase('', 1, 2),
			"procedures" => array_map(function ($v) {
				return ProceduresCtrl::generateProcedure(rand(1, 50));
			}, array_fill(0, $procedures_count, null)),
		];
		if ($is_deleted) {
			$appoinment['is_deleted'] = true;
			$deleted_date = clone $date;
			$deleted_date->sub(new \DateInterval('P' . rand(1, 5) . 'D'));
			$appoinment['deleted_date'] = $deleted_date->format('Y-m-d');
		}
		if (!(rand(1, 3) % 3)) { $appoinment['note'] = Utils::generatePhrase('', 1, rand(1, 21)); }
		if (!(rand(1, 3) % 3)) { $appoinment['address'] = Utils::getRandomAddress(); }
		return $appoinment;
	}
}