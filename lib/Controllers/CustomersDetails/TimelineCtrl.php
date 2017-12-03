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
		return $response->withJson($this->timelineGenericMethod('appointments', $params['start'], $params['end']));
	}
	public function getGallery (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('gallery', $params['start'], $params['end']));
	}
	public function getDepts (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('dept', $params['start'], $params['end']));
	}
	public function getNotes (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('note', $params['start'], $params['end']));
	}
	public function getSms (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('sms', $params['start'], $params['end']));
	}

	private function generateAppointments(\DateTime $date) {
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
	private function generateGallery(\DateTime $date): array {
		$media = [
			"id" => rand(1, 1000),
			"date" => $date->format('Y-m-d'),
			"name" => Utils::generatePhrase('', 1, 1) . '.jpg',
		];
		if (!(rand(1, 3) % 3)) { $media['note'] = Utils::generatePhrase('', 1, rand(1, 21)); }
		return $media;
	}
	private function generateDept(\DateTime $date): array {
		$is_note = !(rand(1, 4) % 4);

		$dept = [
			"id" => rand(1, 1000),
			"sum" => (string)rand(1,30) . (rand(1,2) % 2 ? '5' : '0'),
			"date" => $date->format('Y-m-d'),
		];
		if ($is_note) {
			$dept['desc'] = Utils::generatePhrase('', 1, 21);
		}
		return $dept;
	}
	private function generateSms(\DateTime $date): array {
		return [
			"id" => rand(1, 1000),
			"date" => $date->format('Y-m-d'),
			"text" => Utils::generatePhrase('', 1, 21),
		];
	}
	private function generateNote(\DateTime $date): array {
		$reminder = !(rand(1, 4) % 4);
		$note = [
			"id" => rand(1, 1000),
			"date" => $date->format('Y-m-d'),
			"text" => Utils::generatePhrase('', 1, 21),
		];
		if ($reminder) {
			$reminder_date = clone $date;
			$reminder_date->add(new \DateInterval('P' . rand(1, 15) . 'D'));
			$note['reminder'] = $reminder;
			$note['reminder_date'] = $reminder_date->format('Y-m-d');
		}
		return $note;
	}

	private function timelineGenericMethod ($name, $start, $end): array {
		$start = new \DateTime(filter_var($start, FILTER_SANITIZE_STRING));
		$end = new \DateTime(filter_var($end, FILTER_SANITIZE_STRING));

		$period = new \DatePeriod($start, new \DateInterval('P1D'), $end->add(new \DateInterval('P1D'))); // DatePeriod returns collection of dates excludes the end date

		$dates = array_values(array_filter(iterator_to_array($period), function () {
			return rand(1,2) % 2;
		}));

		$data = array_map(function (\DateTime $date) use ($name) {
			return $this->{'generate' . ucfirst($name)}($date);
		}, $dates);

		return ["name" => $name, "data" => $data, "is_end" => !(rand(1, 5) % 5)];
	}
}