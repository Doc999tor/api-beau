<?php

namespace Lib\Controllers;

use \Lib\Controllers\Controller as Controller;
use \Lib\Controllers\ServicesCtrl as ServicesCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Lib\Helpers\Utils as Utils;

class AppointmentsCtrl extends Controller {
	public function getCalendar (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		# category_id
		if (isset($params['category_id'])) {
			if (ctype_digit($params['category_id'])) {
				return $response->withJson($this->createAppointments());
			} else {
				$response->getBody()->write('category_id has to be an integer');
				return $response->withStatus(400);
			}
		} else if (isset($params['service_id'])) {
			$ids = json_decode('[' . $params['service_id'] . ']');
			if (empty(array_filter($ids, function ($v) {
				return !is_numeric($v);
			}))) {
				return $response->withJson($this->createAppointments());
			} else {
				$response->getBody()->write('service_id has to be an integers list');
				return $response->withStatus(400);
			}
		}

		# regular appointments api
		if (
			(
				!isset($params['start']) || (!\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $params['start']) && !\DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $params['start']))
			) || (
				!isset($params['end']) || (!\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $params['end']) && !\DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $params['end']))
			)
		) {
			$response->getBody()->write('start and end have to exist and to be UTC format, like 2017-12-18T02:09:54.486Z');
			return $response->withStatus(400);
		} else {
			if (!isset($params['worker_id']) || !ctype_digit($params['worker_id'])) {
				$response->getBody()->write('worker_id has to be an integer');
				return $response->withStatus(400);
			} else {
				$start = new \DateTime(filter_var($params['start'], FILTER_SANITIZE_STRING));
				$end = new \DateTime(filter_var($params['end'], FILTER_SANITIZE_STRING));

				$period = new \DatePeriod($start, new \DateInterval('P1D'), $end);

				$appointments = [];
				foreach ($period as $date) {
					$hours_range = range(10, 19);
					shuffle($hours_range);
					$hours = array_slice($hours_range, 0, rand(0, count($hours_range)));
					sort($hours);

					for ($i=0, $count_hours = count($hours); $i < $count_hours; $i++) {
						$datetime = (clone $date)->add(new \DateInterval("PT{$hours[$i]}H" . (rand(0,3)*15) . 'M'));

						$appointments []= $this->generateAppointment($datetime);
					}
				}

				usort($appointments, function ($a, $b) {
					return new \DateTime($a['start']) < new \DateTime($b['start']);
				});

				return $response->withJson($appointments);
			}
		}
	}

	public function edit (Request $request, Response $response, array $args):Response {
		$appointment_id = filter_var($args['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
		$body = $request->getParsedBody();
		// var_dump(isset($body['end']) && !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['end']));

		$response_body = $response->getBody();
		if (isset($body['start'])) {
			if (!\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['start'])) {
				$response_body->write('start has to exist and to be UTC format, like 2017-12-18T02:09:54.486Z');
				return $response->withStatus(400);
			}
		} else {
			if (isset($body['end']) && !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['end'])) {
				$response_body->write('end has to exist and to be UTC format, like 2017-12-18T02:09:54.486Z');
				return $response->withStatus(400);
			} else {
				$response_body->write('body cannot be empty');
				return $response->withStatus(400);
			}
		}

		return $response->withStatus(204);
	}
	public function delete (Request $request, Response $response, array $args):Response {
		// $appointment_id = filter_var($args['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
		return $response->withStatus(204);
	}

	public function getCalendarSettings(Request $request, Response $response) {
		return $response->withJson([
			'slotDuration' => '00:15:00',
			'minTime' => str_pad(8 + rand(-1, 1), 2, '0', STR_PAD_LEFT) . ':00',
			'maxTime' => (22 + rand(-1, 1)) . ':00',
			'firstDay' => 0,
			'eventOverlap' => true,
		]);
	}

	private function generateAppointment(\DateTime $start) {
		$services_count = rand(1, 5);
		$appointment = [
			"id" => rand(1, 1000),
			'name' => Utils::generatePhrase('', 1, 3),
			"start" => $start->format('Y-m-d\TH:i:s\Z'),
			'end' => (clone $start)->add(new \DateInterval('PT' . rand(0, 3) .'H' . rand(0,1)*30 . 'M'))->format('Y-m-d\TH:i:s\Z'),
			'price' => rand(0,50)*10,
			'profile_picture' => Utils::generateWord() . '.jpg',
			'phone' => '0' . rand(1,9) . '-' . implode(array_map(function ($v) {
				return rand(0, 9);
			}, array_fill(0, 8, 0))),
			"client_id" => rand(1, 120),
			'birthdate' => ((new \DateTime())->sub(new \DateInterval('P' . (6000 + rand(0,14000)) . 'D')))->format('Y-m-d'), // new date between 15-50 years ago
			"services" => array_map(function ($v) {
				return ServicesCtrl::generateServiceCalendar(rand(1, 50));
			}, array_fill(0, $services_count, null)),
			'price' => rand(0, 14) * 30,
			'is_reminders_set' => (bool)rand(0,1),
		];
		$duration_obj = (new \DateTime($appointment['start']))->diff(new \DateTime($appointment['end']));
		$appointment['duration'] = $duration_obj->days * 24 * 60 + $duration_obj->h * 60 + $duration_obj->i;
		if (rand(0,1)) {
			$appointment['address'] = Utils::getRandomAddress();
		}
		if (rand(0,1)) {
			$appointment['note'] = Utils::generatePhrase('', 0, 15);
		}

		return $appointment;
	}

	public function addAppointment (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkAppointmentCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function addMeeting (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkMeetingCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function addBreak (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBreakCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function addVacation (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBreakCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkAppointmentCorrectness (array $body): array {
		$correct_body = ['start', 'client_id', 'services', 'duration', 'is_reminders_set', 'note', 'price', 'address', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		if (!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['start'])) { $is_correct = false; $msg .= ' start has to be UTC format, like 2017-12-18T02:09:54.486Z <br>'; }

		if (!preg_match('/^-?\d+$/', $body['client_id'])) { $is_correct = false; $msg .= 'client_id has to be a positive integer or -1 for occasional client <br>'; }

		$services = json_decode($body['services']);
		if (gettype($services) !== 'array' || count(array_filter($services, function ($s) {
			return is_int($s->id) && (empty($s->count) || (!empty($s->count) && is_int($s->count)));
		})) !== count($services)) { $is_correct = false; $msg .= ' services have to be an array of {id: int, count: int} <br>'; }
		if (!isset($body['duration']) || !ctype_digit($body['duration'])) {$is_correct = false; $msg .= ' duration has to be an integer <br>'; }

		if (!in_array($body['is_reminders_set'], ['true', 'false'])) {$is_correct = false; $msg .= ' is_reminders_set has be be true or false <br>'; }

		if (!isset($body['price']) || !is_numeric($body['price'])) {$is_correct = false; $msg .= ' price has to be an integer <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['added'])) { $is_correct = false; $msg .= ' added has to be UTC format, like 2017-12-18T02:09:54.486Z <br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function checkMeetingCorrectness (array $body): array {
		$correct_body = ['start', 'end', 'is_all_day', 'note', 'address', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		if ((!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['start'])) || (!isset($body['end']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['end']))) { $is_correct = false; $msg .= 'start and end have to exist and to be UTC format, like 2017-12-18T02:09:54.486Z <br>'; }

		if (isset($body['is_all_day']) && !in_array($body['is_all_day'], ['true', 'false'])) {$is_correct = false; $msg .= ' is_all_day can be true or false only <br>'; }
		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['added'])) { $is_correct = false; $msg .= ' added has to be UTC format, like 2017-12-18T02:09:54.486Z <br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function checkBreakCorrectness (array $body): array {
		$correct_body = ['start', 'end', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		if ((!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['start'])) || (!isset($body['end']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['end']))) { $is_correct = false; $msg .= 'start and end have to exist and to be UTC format, like 2017-12-18T02:09:54.486Z <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['added'])) { $is_correct = false; $msg .= ' added has to be UTC format, like 2017-12-18T02:09:54.486Z <br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function createAppointments () {
		$appointments = [];
		if (rand(1,3) % 3 !== 0) {
			$range_dates = range(1, rand(5,50), rand(1,5));
			shuffle($range_dates);
			$range_dates = array_slice($range_dates, 0, rand(1,20));
			sort($range_dates);

			$today = new \DateTime();
			$appointments = [];
			for ($i=0, $appointments_limit = count($range_dates); $i < $appointments_limit; $i++) {
				$interval = new \DateInterval("P{$range_dates[$i]}D");
				$date = (clone $today)->sub($interval);
				$appointments []= $this->generateAppointment($date);
			}
		}
		return $appointments;
	}
}