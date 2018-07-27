<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NotesCtrl extends Controller {
	public function addNote (Request $request, Response $response) {
		$body = $request->getParsedBody();

		$error_msg = self::checkCorrectnessBody($body);
		if ($error_msg) {
			$body = $response->getBody();
			$body->write($error_msg);
			return $response->withStatus(400);
		}

		return $response->withStatus(201);
	}
	public function updateNote (Request $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		$error_msg = self::checkCorrectnessBody($body);
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

	public static function checkCorrectnessBody(array $body):string {
		$error_msg = '';

		if (!isset($body['text'])) {
			$error_msg .= 'text param has to be, it can be empty string <br>';
		} else if (mb_strlen($body['text']) < 3) {
			$error_msg .= 'text has to be bigger than 2 chars <br>';
		}
		if (isset($body['reminder_date']) && !\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $body['reminder_date'])) {
			$error_msg .= 'reminder_date has to be UTC format, like  2017-12-18T02:09:54.486Z<br>';
		}

		return $error_msg;
	}
}