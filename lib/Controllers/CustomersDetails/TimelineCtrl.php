<?php

namespace Lib\Controllers\CustomersDetails;

use \Lib\Helpers\Utils as Utils;
use Lib\Controllers\Controller as Controller;
use Lib\Controllers\ServicesCtrl as ServicesCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class TimelineCtrl extends Controller {
	public function getAppointments (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('appointments'));
	}
	public function getGallery (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('gallery'));
	}
	public function getDepts (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('depts'));
	}
	public function getNotes (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('notes'));
	}
	public function getSms (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('sms'));
	}
	public function getPunchCards (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		return $response->withJson($this->timelineGenericMethod('punch_cards'));
	}

	private function generateAppointments(\DateTime $date, int $id) {
		$services_count = rand(1, 5);
		$is_deleted = !(rand(1, 5) % 5);

		$added_date = clone $date;
		$added_date->sub(new \DateInterval('P' . rand(3, 10) . 'D'));

		$total_price = rand(0,50) * 10;
		$prepayment_chances = rand(1,3);
		if ($prepayment_chances === 1) {
			$prepayment = $total_price;
		} else if ($prepayment_chances === 2) {
			$prepayment = rand(0, $total_price);
		} else { $prepayment = 0; }

		$end = (clone $date)->add(new \DateInterval('PT' . (rand(1,12)*15) . 'M'));

		$appointment = [
			"id" => $id,
			"start" => $date->format('Y-m-d H:i'),
			"date" => $date->format('Y-m-d H:i'),
			"end" => $end->format('Y-m-d H:i'),
			"added_date" => $added_date->format('Y-m-d H:i'),
			"worker_id" => rand(1, 5),
			"worker_name" => Utils::generatePhrase('', 1, 2),
			"worker_profile_image" => (rand(1,2)%2 ? 1 : Utils::generatePhrase('', 1, 2)) . '.jpg',
			"services" => array_map(function ($v) {
				$appointment = ServicesCtrl::generateService(rand(1, 50));
				$appointment['count'] = rand(1,3)>1 ? 1 : rand(1,40);
				return $appointment;
			}, array_fill(0, $services_count, null)),
			"total_price" => $total_price,
			"prepayment" => $prepayment,
		];
		$appointment['price_before_discount'] = (string) (rand(0,3) ? round($appointment['total_price'] / (rand(70, 99) / 100 )) : $appointment['total_price']);
		if ($is_deleted) {
			$appointment['is_deleted'] = true;
			$deleted_date = clone $date;
			$deleted_date->sub(new \DateInterval('P' . rand(1, 5) . 'D'));
			$appointment['deleted_date'] = $deleted_date->format('Y-m-d H:i');
		}
		if (!(rand(1, 3) % 3)) { $appointment['note'] = Utils::generatePhrase('', 1, rand(1, 21)); }
		if (!(rand(1, 3) % 3)) { $appointment['location'] = Utils::getRandomAddress(); }
		return $appointment;
	}
	private function generateGallery(\DateTime $date, int $id): array {
		$media = [
			"id" => $id,
			"date" => $date->format('Y-m-d H:i'),
			"name" => Utils::generatePhrase('', 1, 1) . '.jpg',
		];
		if (!(rand(1, 3) % 3)) { $media['note'] = Utils::generatePhrase('', 1, rand(1, 21)); }
		return $media;
	}
	private function generateDepts(\DateTime $date, int $id): array {
		$is_note = !(rand(1, 4) % 4);

		$is_deleted = !(rand(1, 5) % 5);
		$deleted_date = clone $date;
		$deleted_date->sub(new \DateInterval('P' . rand(3, 10) . 'D'));

		$is_modified = !(rand(1, 5) % 5);
		$modified_date = clone $date;
		$modified_date->sub(new \DateInterval('P' . rand(3, 10) . 'D'));

		$dept = [
			"id" => $id,
			"sum" => (string)rand(1,30) . (rand(1,2) % 2 ? '5' : '0'),
			"date" => $date->format('Y-m-d H:i'),
		];
		if ($is_note) { $dept['desc'] = Utils::generatePhrase('', 1, 21); }
		if ($is_modified) { $dept['modified_date'] = $modified_date->format('Y-m-d H:i'); }
		if ($is_deleted) {
			$dept['is_deleted'] = $is_deleted;
			$dept['deleted_date'] = $deleted_date->format('Y-m-d H:i');
		}
		return $dept;
	}
	private function generatePunch_cards(\DateTime $date, int $id) {
		$service_count = rand(3, 10);
		$is_punch_created = !(rand(1,5) % 5);
		$punch_card = [
			'id' => $id,
			'date' => $date->format('Y-m-d H:i'),
			'service_name' => Utils::generatePhrase('', 1, 3),
			'service_count' => $service_count,
			'service_color' => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)),
			'use_id' => $is_punch_created ? null : rand(1, $service_count),
			'event_type' => $is_punch_created ? 'punch_card_created' : 'punch_card_used',
		];
		return $punch_card;



		// $date_kind = rand(1, 3);
		// switch ($date_kind) {
		// 	case 1:
		// 		// punch_card creation
		// 		$punch_card['date'] = $date->format('Y-m-d H:i');
		// 		$punch_card['uses'] = [];
		// 		break;
		// 	case 2:
		// 		// punch_card use
		// 		$added_interval = rand(3, 10);
		// 		$added_date = clone $date;
		// 		$added_date->sub(new \DateInterval("P{$added_interval}D"));
		// 		$punch_card['date'] = $added_date->format('Y-m-d H:i');
		// 		$punch_card['uses'] = [
		// 			["id" => rand(1, 150), "date" => $date->format('Y-m-d H:i'),],
		// 		];
		// 		break;
		// 	case 3:
		// 		// punch_card creation
		// 		$added_interval = rand(3, 10);
		// 		$added_date = clone $date;
		// 		$added_date->sub(new \DateInterval("P{$added_interval}D"));
		// 		$punch_card['date'] = $added_date->format('Y-m-d H:i');
		// 		$use_date = clone $date;
		// 		$use_date->sub(new \DateInterval('P' . rand(1, $added_interval - 1) . 'DT' . rand(8, 16) . 'H'));
		// 		$punch_card['uses'] = [
		// 			["id" => rand(1, 150), "date" => $use_date->format('Y-m-d H:i'),],
		// 		];
		// 		$punch_card['expiration'] = $date->format('Y-m-d H:i');
		// 		break;
		// }
		// return $punch_card;
	}
	private function generateSms(\DateTime $date, int $id): array {
		return [
			"id" => $id,
			"date" => $date->format('Y-m-d H:i'),
			"text" => Utils::generatePhrase('', 1, 21),
		];
	}
	private function generateNotes(\DateTime $date, int $id): array {
		// $reminder = !(rand(1, 4) % 4);
		$note = [
			"id" => $id,
			"date" => $date->format('Y-m-d H:i'),
			"text" => Utils::generatePhrase('', 1, 21),
		];
		// if ($reminder) {
		// 	$reminder_date = clone $date;
		// 	$reminder_date->add(new \DateInterval('P' . rand(1, 15) . 'D'));
		// 	$note['reminder'] = $reminder;
		// 	$note['reminder_date'] = $reminder_date->format('Y-m-d H:i');
		// }
		return $note;
	}

	private function timelineGenericMethod (string $name): array {
		// $start = new \DateTime(filter_var($start, FILTER_SANITIZE_STRING));
		// $end = new \DateTime(filter_var($end, FILTER_SANITIZE_STRING));

		// $dates_collection = new \DatePeriod($start, new \DateInterval('P1D'), $end->add(new \DateInterval('P1D'))); # DatePeriod returns collection of dates excludes the end date

		$start = (new \DateTime())->setTime(10,0)->sub(new \DateInterval('P' . rand(0,200) . 'D'));
		$end = $name === 'appointments'
			? (new \DateTime())->add(new \DateInterval('P' . rand(0,10) . 'D'))
			: new \DateTime();
		$dates_collection = new \DatePeriod($start, new \DateInterval('P1D'), $end);

		$dates = array_map(function ($date) {
			return $date->add(new \DateInterval('PT' . (rand(0,16) * 30) . 'M')); # adds some random times between 10:00-18:00
		}, array_values(array_filter(iterator_to_array($dates_collection), function () {
			return rand(0,1);
		})));

		$data = array_map(function (\DateTime $date, $id) use ($name) {
			return $this->{'generate' . ucfirst($name)}($date, $id + 1);
		}, $dates, array_keys($dates));

		return $data; # [data => []] is deprecated
	}
}
