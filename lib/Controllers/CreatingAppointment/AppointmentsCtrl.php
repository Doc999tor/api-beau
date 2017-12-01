<?php

namespace Lib\Controllers\CreatingAppointment;

use Lib\Controllers\Controller as Controller;
use Lib\Controllers\ProceduresCtrl as ProceduresCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AppointmentsCtrl extends Controller {
	public function getCalendar (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		if ((!isset($params['start']) || !\DateTime::createFromFormat('Y-m-d H:i', $params['start'])) || (!isset($params['end']) || !\DateTime::createFromFormat('Y-m-d H:i', $params['end']))) {
			$response->getBody()->write('start and end have to exist and to be Y-m-d H:i format, like 1970-01-01 00:00');
			return $response->withStatus(400);
		} else {
			if (!isset($params['worker_id']) || !ctype_digit($params['worker_id'])) {
				$response->getBody()->write('worker_id has to be an integer');
				return $response->withStatus(400);
			} else {
				$appointments = [];
				$appointments_limit = rand() % 4 !== 0 ? 0 : rand(1, 5);
				for ($i=0; $i < $appointments_limit; $i++) {
					$appointments []= $this->generateAppointment($params['start']);
				}
				return $response->withJson($appointments);
			}
		}
	}

	private function generateAppointment($date) {
		$procedures_count = rand(1, 5);
		return [
			"id" => rand(1, 1000),
			"date" => $date,
			"client_id" => rand(1, 120),
			"procedures" => array_map(function ($v) {
				return ProceduresCtrl::generateProcedure(rand(1, 50));
			}, array_fill(0, $procedures_count, null))
		];
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
		$correct_body = ["start", "client_id", "procedures", "duration", "is_reminders_set", "note", "address", "worker_id"];

		$is_correct = true; $msg = '';

		if (!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d H:i', $body['start'])) { $is_correct = false; $msg .= ' start has to be Y-m-d H:i format, like 1970-01-01 00:00 <br>'; }

		if (!preg_match('/^-?\d+$/', $body['client_id'])) { $is_correct = false; $msg .= 'client_id has to be a positive integer or -1 for occasional client <br>'; }

		$procedures = json_decode($body['procedures']);
		if (gettype($procedures) !== 'array' || count(array_filter($procedures, 'is_int')) !== count($procedures)) { $is_correct = false; $msg .= ' procedures have to be an array of integers <br>'; }
		if (!isset($body['duration']) || !ctype_digit($body['duration'])) {$is_correct = false; $msg .= ' duration has to be an integer <br>'; }

		if (!in_array($body['is_reminders_set'], ['true', 'false'])) {$is_correct = false; $msg .= ' is_reminders_set has be be true or false <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function checkMeetingCorrectness (array $body): array {
		$correct_body = ["start", "end", "is_all_day", "note", "address", "worker_id"];

		$is_correct = true; $msg = '';

		if ((!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d H:i', $body['start'])) || (!isset($body['end']) || !\DateTime::createFromFormat('Y-m-d H:i', $body['end']))) { $is_correct = false; $msg .= ' start and end have to exist and to be Y-m-d H:i format, like 1970-01-01 00:00 <br>'; }

		if (isset($body['is_all_day']) && !in_array($body['is_all_day'], ['true', 'false'])) {$is_correct = false; $msg .= ' is_all_day can be true or false only <br>'; }
		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function checkBreakCorrectness (array $body): array {
		$correct_body = ["start", "end", "worker_id"];

		$is_correct = true; $msg = '';

		if ((!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d H:i', $body['start'])) || (!isset($body['end']) || !\DateTime::createFromFormat('Y-m-d H:i', $body['end']))) { $is_correct = false; $msg .= ' start and end have to exist and to be Y-m-d H:i format, like 1970-01-01 00:00 <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
}