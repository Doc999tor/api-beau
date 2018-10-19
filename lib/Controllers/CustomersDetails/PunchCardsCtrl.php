<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PunchCardsCtrl extends Controller {
	public function get (Request $request, Response $response):Response {
		$punch_cards = [];

		for ($i=0, $punch_cards_count = rand(1,3); $i < $punch_cards_count; $i++) {
			$punch_cards []= $this->generatePunchCard();
		}
		$punch_cards[0]['service_id'] = 1;

		return $response->withJson($punch_cards);
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
		if (!\DateTime::createFromFormat('Y-m-d H:i:s', $time)) { $is_correct = false; $msg .= " date has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>"; }

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
		if (isset($body['date']) && !\DateTime::createFromFormat('Y-m-d H:i:s', $body['date'])) { $is_correct = false; $msg .= "date has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>"; }

		if (isset($body['expiration']) && !\DateTime::createFromFormat('Y-m-d', $body['expiration'])) { $is_correct = false; $msg .= 'expiration has to be Y-m-d format, like 1970-01-01' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	public function generatePunchCard() {
		$punch_card = [ "id" => rand(1, 51) ];

		$service = \Lib\Controllers\ServicesCtrl::generateService(rand(1, 45));
		$punch_card['service_name'] = $service['name'];
		$punch_card['service_id'] = $service['id'];

		$possible_service_counts = [3, 5, 7, 10, 20];
		$punch_card['service_count'] = $possible_service_counts[array_rand($possible_service_counts)];
		$punch_card['sum'] = ($punch_card['service_count'] - 1*rand(0,1)) * $service['price'];

		$punch_card['date'] = (new \DateTime())
			->modify(rand(0,180) . ' days ago')
			->format('Y-m-d');

		if (rand() % 4) {
			$punch_card['uses'] = [];
			$uses_count = rand(1, $punch_card['service_count'] - 1);

			for ($i=1; $i <= $uses_count; $i++) {
				$punch_card['uses'] []= [
					"id" => $i,
					"date" => (new \DateTime($punch_card['date']))
						->modify('-' . $i . ' months')
						->modify('first day of')
						->format('Y-m-d H:i:s')
				];
			}
		}

		if (rand(1, 3) % 3) {
			$punch_card['expiration'] = (new \DateTime())
				->modify('+' . rand(0,6) . ' month')
				->modify('last day of')
				->format('Y-m-d');
		}
		return $punch_card;
	}
}