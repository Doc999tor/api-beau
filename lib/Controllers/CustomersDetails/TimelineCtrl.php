<?php

namespace Lib\Controllers\CustomersDetails;

use \Lib\Helpers\Utils as Utils;
use Lib\Controllers\Controller as Controller;
use Lib\Controllers\ServicesCtrl as ServicesCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class TimelineCtrl extends Controller {
	public function getAppoinments (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('appointments', $params['start'], $params['end']), 200, JSON_PRETTY_PRINT );
	}
	public function getGallery (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('gallery', $params['start'], $params['end']), 200, JSON_PRETTY_PRINT );
	}
	public function getDepts (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('depts', $params['start'], $params['end']), 200, JSON_PRETTY_PRINT );
	}
	public function getNotes (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('notes', $params['start'], $params['end']), 200, JSON_PRETTY_PRINT );
	}
	public function getSms (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('sms', $params['start'], $params['end']), 200, JSON_PRETTY_PRINT );
	}
	public function getPunchCards (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('punch_cards', $params['start'], $params['end']), 200, JSON_PRETTY_PRINT );
	}

	private function generateAppointments(\DateTime $date) {
		$services_count = rand(1, 5);
		$is_deleted = !(rand(1, 5) % 5);

		$added_date = clone $date;
		$added_date->sub(new \DateInterval('P' . rand(3, 10) . 'D'));

		$end = (clone $date)->add(new \DateInterval('PT' . (rand(1,12)*15) . 'M'));

		$appoinment = [
			"id" => rand(1, 1000),
			"start" => $date->format('Y-m-d H:i'),
			"end" => $end->format('Y-m-d H:i'),
			"added_date" => $added_date->format('Y-m-d'),
			"worker_id" => rand(1, 5),
			"worker_name" => Utils::generatePhrase('', 1, 2),
			"worker_profile_image" => (rand(1,2)%2 ? 1 : Utils::generatePhrase('', 1, 2)) . '.jpg',
			"services" => array_map(function ($v) {
				return ServicesCtrl::generateService(rand(1, 50));
			}, array_fill(0, $services_count, null)),
		];
		if ($is_deleted) {
			$appoinment['is_deleted'] = true;
			$deleted_date = clone $date;
			$deleted_date->sub(new \DateInterval('P' . rand(1, 5) . 'D'));
			$appoinment['deleted_date'] = $deleted_date->format('Y-m-d');
		}
		if (!(rand(1, 3) % 3)) { $appoinment['note'] = Utils::generatePhrase('', 1, rand(1, 21)); }
		if (!(rand(1, 3) % 3)) { $appoinment['location'] = Utils::getRandomAddress(); }
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
	private function generateDepts(\DateTime $date): array {
		$is_note = !(rand(1, 4) % 4);

		$is_deleted = !(rand(1, 5) % 5);
		$deleted_date = clone $date;
		$deleted_date->sub(new \DateInterval('P' . rand(3, 10) . 'D'));

		$is_modified = !(rand(1, 5) % 5);
		$modified_date = clone $date;
		$modified_date->sub(new \DateInterval('P' . rand(3, 10) . 'D'));

		$dept = [
			"id" => rand(1, 1000),
			"sum" => (string)rand(1,30) . (rand(1,2) % 2 ? '5' : '0'),
			"date" => $date->format('Y-m-d'),
		];
		if ($is_note) { $dept['desc'] = Utils::generatePhrase('', 1, 21); }
		if ($is_modified) { $dept['modified_date'] = $modified_date->format('Y-m-d'); }
		if ($is_deleted) {
			$dept['is_deleted'] = $is_deleted;
			$dept['deleted_date'] = $deleted_date->format('Y-m-d');
		}
		return $dept;
	}
	private function generatePunch_cards(\DateTime $date) {
		$services_count = 10;
		$punch_card = [
			"id" => rand(1, 10),
			"service_name" => Utils::generatePhrase('', 1, 3),
			"service_id" => rand(1, 50),
			"service_count" => $services_count,
		];
		$date_kind = rand(1, 3);
		switch ($date_kind) {
			case 1:
				// punch_card creation
				$punch_card['date'] = $date->format('Y-m-d');
				$punch_card['uses'] = [];
				break;
			case 2:
				// punch_card use
				$added_interval = rand(3, 10);
				$added_date = clone $date;
				$added_date->sub(new \DateInterval("P{$added_interval}D"));
				$punch_card['date'] = $added_date->format('Y-m-d');
				$punch_card['uses'] = [
					["id" => rand(1, 150), "date" => $date->format('Y-m-d H:m'),],
				];
				break;
			case 3:
				// punch_card creation
				$added_interval = rand(3, 10);
				$added_date = clone $date;
				$added_date->sub(new \DateInterval("P{$added_interval}D"));
				$punch_card['date'] = $added_date->format('Y-m-d');
				$use_date = clone $date;
				$use_date->sub(new \DateInterval('P' . rand(1, $added_interval - 1) . 'DT' . rand(8, 16) . 'H'));
				$punch_card['uses'] = [
					["id" => rand(1, 150), "date" => $use_date->format('Y-m-d H:m'),],
				];
				$punch_card['expiration'] = $date->format('Y-m-d');
				break;
		}
		return $punch_card;
	}
	private function generateSms(\DateTime $date): array {
		return [
			"id" => rand(1, 1000),
			"date" => $date->format('Y-m-d'),
			"text" => Utils::generatePhrase('', 1, 21),
		];
	}
	private function generateNotes(\DateTime $date): array {
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

		$period = new \DatePeriod($start, new \DateInterval('P1D'), $end->add(new \DateInterval('P1D'))); # DatePeriod returns collection of dates excludes the end date

		$dates = array_map(function ($date) {
			return $date->add(new \DateInterval('PT' . ((rand(0,18)+18)*30) . 'M')); # adds some random times between 10:00-18:00
		}, array_values(array_filter(iterator_to_array($period), function () {
			return rand(1,2) % 2;
		})));

		$data = array_map(function (\DateTime $date) use ($name) {
			return $this->{'generate' . ucfirst($name)}($date);
		}, $dates);

		return ["name" => $name, "data" => $data, "is_end" => !(rand(1, 5) % 5)]; # temporary for Mykola request
		// return $data;
	}
}