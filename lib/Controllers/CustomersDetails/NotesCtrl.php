<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NotesCtrl extends Controller {
	public function addNote (Request $request, Response $response) {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		if ($this->checkCorrectness($body)) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("text has to be bigger than 2 chars, \n reminder has to be a true or false, \n 'date has to be in Y-m-d H:i format, 1970-01-01 06:00 for example");
			return $response->withStatus(400);
		}
	}
	public function updateNote (Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$error_msg = '';

		if (isset($body['text']) && mb_strlen($body['text']) < 3) {
			$error_msg = 'text has to be bigger than 2 chars';
		}
		if (isset($body['reminder']) && !in_array($body['reminder'], ['false', 'true'])) {
			$error_msg = 'reminder has to be a true or false';
		}
		if (isset($body['reminder_date']) && \DateTime::createFromFormat('Y-m-d H:i', $body['reminder_date']) === false) {
			$error_msg = 'date has to be in Y-m-d H:i format, 1970-01-01 06:00 for example';
		}

		if ($error_msg) {
			$body = $response->getBody();
			$body->write($error_msg);
			return $response->withStatus(400);
		}

		return $response->withStatus(204);
	}
	public function deleteNote (Request $request, Response $response) {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}

	private function checkCorrectness(array $body):bool {
		if (isset($body['text']) && mb_strlen($body['text']) > 2) {
			if (!isset($body['reminder'])) {
				return true;
			} else if (isset($body['reminder']) && (in_array($body['reminder'], ['false', 'true'])) && isset($body['reminder_date']) && \DateTime::createFromFormat('Y-m-d H:i', $body['reminder_date'])) {
				return true;
			} else {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}
}