<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PunchCardsCtrl extends Controller {
	public function addPunchCard (Request $request, Response $response) {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function deletePunchCard (Request $request, Response $response) {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}

	private function checkBodyCorrectness($body) {
		$correct_body = ['procedure_id', 'uses', 'sum'];

		$is_correct = true;
		$msg = '';

		if (!empty(array_diff($correct_body, array_keys($body)))) {
			$is_correct = false;
			$msg = implode(', ', array_diff($correct_body, array_keys($body))) . ' argument should exist';
		}

		if (isset($body['procedure_id']) && !ctype_digit($body['procedure_id'])) { $is_correct = false; $msg .= 'procedure_id has to be an integer' . "<br>"; }
		if (isset($body['uses']) && !ctype_digit($body['uses'])) { $is_correct = false; $msg .= 'uses have to be an integer' . "<br>"; }
		if (isset($body['sum']) && !ctype_digit($body['sum'])) { $is_correct = false; $msg .= 'sum has to be an integer' . "<br>"; }

		if (isset($body['expiration']) && !\DateTime::createFromFormat('Y-m-d', $body['expiration'])) { $is_correct = false; $msg .= 'expiration has to be Y-m-d format, like 1970-01-01' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
}