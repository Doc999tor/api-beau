<?php

namespace Lib\Controllers;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class RemindersCtrl extends Controller {
	public function index (Request $request, Response $response):Response {return $response;}

	public function add (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body['is_done'] = 'false';

		list($is_correct, $msg) = $this->checkCorrectness($body);
		if (!$is_correct) {
			$body = $response->getBody();
			$body->write($msg);
			return $response->withStatus(400);
		} else { return $response->withStatus(201); }
	}

	public function update (Request $request, Response $response, array $args):Response {
		$body = $request->getParsedBody();

		list($is_correct, $msg) = $this->checkCorrectness($body);
		if (!$is_correct) {
			$body = $response->getBody();
			$body->write($msg);
			return $response->withStatus(400);
		} else { return $response->withStatus(204); }
	}

	public function isDone (Request $request, Response $response):Response {
		$request_body = $request->getBody();
		$request_body_msg = $request_body->getContents();
		if ($request_body_msg !== 'is_done=true') {
			$response_body = $response->getBody();
			$response_body->write('body is incorrect - ' . $request_body_msg);
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}

	public function delete (Request $request, Response $response, array $args):Response {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		}
		return $response->withStatus(204);
	}

	public function getClients (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$q = $body['q'] ?? '';

		$clients_length = 50;
		$clients_manager = new \Lib\Controllers\AddClient\ClientsCtrl($this->container);
		$clients_list = $clients_manager->generateClients(50, $q);
		return $response->withJson($clients_list);
	}

	private function checkCorrectness ($body) {
		$msg = ''; $is_correct = true;

		if (!mb_strlen($body['text'])) { $msg .= '<br> text has to be one letter at least'; $is_correct = false; }
		if (!in_array($body['is_done'], ['true', 'false'])) { $msg .= '<br> is_done has to be true or false'; $is_correct = false; }
		if (isset($body['client_id']) && !ctype_digit($body['client_id'])) { $msg .= '<br> client_id has to be integer'; $is_correct = false; }
		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $msg .= 'added has to be YYYY-MM-DD hh:mm:ss format, like  2017-12-18 02:09:54<br>'; }
		if (!\DateTime::createFromFormat('Y-m-d H:i:s', $body['reminder_date'])) { $msg .= '<br> reminder_date has to be in YYYY-MM-DD hh:mm:ss format, 2018-07-28T15:30:40.5057Z <br>'; $is_correct = false; }

		return [$is_correct, $msg];
	}
}