<?php

namespace Lib\Controllers\CustomersDetails;

use Slim\Container as Container;
use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PunchCardsCtrl extends Controller {
	private $faker;

	function __construct(Container $container) {
		parent::__construct($container);
		$this->faker = \Faker\Factory::create();
	}

	public function get (Request $request, Response $response):Response {
		$punch_cards = [];
		if (!rand(0,3)) {
			return $response->withJson([]);
		}

		for ($i=0, $punch_cards_count = rand(1,5); $i < $punch_cards_count; $i++) {
			$punch_cards []= $this->generatePunchCard($i+1);
		}
		$punch_cards[0]['id'] = 1;
		$punch_cards[0]['service_id'] = 1;

		return $response->withJson($punch_cards);
	}

	public function add (Request $request, Response $response) {
		$body = json_decode($request->getBody()->getContents(), true);
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
		$correct_body = ['service_id', 'service_count', 'sum', 'sum_before_discount', 'note', 'added', 'expiration'];

		$is_correct = true;
		$msg = '';

		if (!empty(array_diff($correct_body, array_keys($body)))) {
			$is_correct = false;
			$msg = implode(', ', array_diff($correct_body, array_keys($body))) . ' argument should exist';
		}

		if (isset($body['service_id']) && !ctype_digit($body['service_id'])) { $is_correct = false; $msg .= 'service_id has to be an integer' . "<br>"; }
		if (isset($body['service_count']) && !ctype_digit($body['service_count'])) { $is_correct = false; $msg .= 'service_count has to be an integer' . "<br>"; }
		if (isset($body['sum']) && !ctype_digit($body['sum'])) { $is_correct = false; $msg .= 'sum has to be an integer' . "<br>"; }
		if (isset($body['sum_before_discount']) && !ctype_digit($body['sum_before_discount'])) { $is_correct = false; $msg .= 'sum_before_discount has to be an integer' . "<br>"; }
		if (isset($body['added']) && !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $is_correct = false; $msg .= "added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>"; }

		if (isset($body['expiration']) && !\DateTime::createFromFormat('Y-m-d', $body['expiration'])) { $is_correct = false; $msg .= 'expiration has to be Y-m-d format, like 1970-01-01' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	public function generatePunchCard(int $i) {
		$punch_card = [ "id" => $i ];

		$service = \Lib\Controllers\ServicesCtrl::generateService(rand(1, 45));
		$punch_card['service_name'] = $service['name'];
		$punch_card['service_id'] = $service['id'];
		$punch_card['service_color'] = $service['color'];

		$possible_service_counts = [3, 5, 7, 10, 20];
		$punch_card['service_count'] = $possible_service_counts[array_rand($possible_service_counts)]; // array_rand returns a random key
		$punch_card['sum_before_discount'] = $punch_card['service_count'] * $service['price'];
		$punch_card['sum'] = ($punch_card['service_count'] - 1*rand(0.5, 1)) * $service['price'];

		$punch_card['added'] = (new \DateTime())
			->modify(rand(90, 180) . ' days ago')
			->format('Y-m-d');

		$punch_card['uses'] = [];
		if (rand(0,1)) {
			$uses_count = (new \DateTime())->diff(new \DateTime($punch_card['added']))->m;

			for ($i=1; $i <= $uses_count; $i++) {
				$punch_card['uses'] []= [
					"id" => $i,
					"date" => (new \DateTime($punch_card['added']))
						->modify('+' . $i . ' months')
						->modify('first day of')
						->add(new \DateInterval('PT' . rand(10,20) . 'H'))
						->format('Y-m-d H:i:s')
				];
			}
		}

		if (rand(1, 3) % 3) {
			$punch_card['expiration'] = (new \DateTime())
				->modify('+' . (rand(0,1) ? rand(0,6) : rand(-1,-3)) . ' month')
				->modify('last day of')
				->format('Y-m-d');
		}
		if (!rand(0, 3)) {
			$punch_card['note'] = $this->faker->sentence(rand(1,15));
		}
		return $punch_card;
	}
}
