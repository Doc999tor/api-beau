<?php

namespace Lib\Controllers\CreatingAppointment;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AppointmentsCtrl extends Controller {

	public function add (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkCorrectness (array $body): array {
		$correct_body = ["date", "start", "client_id", "procedures", "is_reminders_set", "note", "address"];

		$is_correct = true;
		$msg = '';

		// if (count($body) !== count($correct_body)) {
		// 	$is_correct = false;
		// 	$msg = 'body has to have ' . count($correct_body) . ' arguments';
		// }

		// if (!empty(array_diff($correct_body, array_keys($body)))) {
		// 	$is_correct = false;
		// 	$msg = implode(', ', array_diff($correct_body, array_keys($body))) . ' argument should exist';
		// }

		if (!\DateTime::createFromFormat('Y-m-d H:i', $body['date'] . ' ' . $body['start'])) {
			$is_correct = false;
			$msg = 'date and start has to be Y-m-d H:i format, like 1970-01-01 00:00';
		}

		if (!preg_match('/^-?\d+$/', $body['client_id'])) {
			$is_correct = false;
			$msg = 'client_id has to be a positive integer or -1 for occasional client';
		}

		$procedures = json_decode($body['procedures']);
		if (gettype($procedures) !== 'array' || count(array_filter($procedures, 'is_int')) !== count($procedures)) {
			$is_correct = false;
			$msg = 'procedures have to be an array of integers';
		}

		if (in_array($body['is_reminders_set'], ['true', 'false'])) {
			$reminders = array_map('intval', json_decode($body['reminders']) ?? []);
			if ($body['is_reminders_set'] === 'true' && (!json_decode($body['reminders']) || count(array_filter($reminders, function ($rem) { return is_int($rem / 3600); })) !== count($reminders))) {
				$is_correct = false;
				$msg = 'reminders is set incorrectly, it has to be seconds multiple of 3600';
			}
		} else {
			$is_correct = false;
			$msg = 'is_reminders_set has to be or true or false';
		}

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
}