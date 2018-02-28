<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PunchCardsCtrl extends Controller {
	public function get (Request $request, Response $response):Response {



		return $response->getBody()->write('response body');
	}

	public function add (Request $request, Response $response) {
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

	public function use (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$time = filter_var($body['date'], FILTER_SANITIZE_STRING);

		$is_correct = true; $msg = '';
		if (!\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $time)) { $is_correct = false; $msg .= " date has to be UTC format, like 2017-12-18T02:09:54.486Z<br>"; }

		if ($is_correct) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $msg);
			return $response->withStatus(400);
		}
	}
	public function unuse (Request $request, Response $response):Response {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}

	private function checkBodyCorrectness($body) {
		$correct_body = ['service_id', 'uses', 'sum', 'date'];

		$is_correct = true;
		$msg = '';

		if (!empty(array_diff($correct_body, array_keys($body)))) {
			$is_correct = false;
			$msg = implode(', ', array_diff($correct_body, array_keys($body))) . ' argument should exist';
		}

		if (isset($body['service_id']) && !ctype_digit($body['service_id'])) { $is_correct = false; $msg .= 'service_id has to be an integer' . "<br>"; }
		if (isset($body['uses']) && !ctype_digit($body['uses'])) { $is_correct = false; $msg .= 'uses have to be an integer' . "<br>"; }
		if (isset($body['sum']) && !ctype_digit($body['sum'])) { $is_correct = false; $msg .= 'sum has to be an integer' . "<br>"; }
		if (isset($body['date']) && !\DateTime::createFromFormat('Y-m-d\Th:i:s.u\Z', $body['date'])) { $is_correct = false; $msg .= "date has to be UTC format, like 2017-12-18T02:09:54.486Z<br>"; }

		if (isset($body['expiration']) && !\DateTime::createFromFormat('Y-m-d', $body['expiration'])) { $is_correct = false; $msg .= 'expiration has to be Y-m-d format, like 1970-01-01' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	public function generatePunchCard() {
		$punch_card = [
			"id" => rand(1, 51),
			"sum" => 450,
			"date" => "2017-01-01",
			"expiration" => "2017-12-31",
		];
		$service = \Lib\Controllers\ServicesCtrl::generateService();
		$punch_card['service_name'] = $service['name'];
		$punch_card['service_id'] = $service['id'];
		$punch_card['service_count'] = array_rand([3, 5, 7, 10, 20]);
		$punch_card['sum'] = ($punch_card['service_count'] - 1*rand(0,1)) * $service['price'];

		$punch_card['date'] = (new \DateTime())
			->modify(rand(0,180) . ' days ago')
			->format('Y-m-d');

		if (rand(1, 3) % 3) {
			$punch_card['expiration'] = (new \DateTime())
				->modify('+' . rand(0,6) . ' month')
				->modify('last day of')
				->format('Y-m-d');
		}
	}
}