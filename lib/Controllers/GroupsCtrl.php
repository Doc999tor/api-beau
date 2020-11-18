<?php

namespace Lib\Controllers;
use Lib\Helpers\Utils;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GroupsCtrl extends Controller {
	public function getGroups (Request $request, Response $response):Response {
		$groups = [];
		for ($i=0; $i < rand(3, 8); $i++) {
			$groups []= $this->generateGroup();
		}
		$groups[0]['id'] = 1;
		$groups[0]['name'] = 'Recent appointments';
		$groups[0]['image_path'] = 'recent_appointments.jpg';
		$groups[0]['amount'] = 8;
		$groups[0]['is_automatic'] = true;
		$groups[1]['id'] = 2;
		$groups[1]['name'] = 'Have not been a while clients';
		$groups[1]['image_path'] = 'have_not_been_a_while_clients.jpg';
		$groups[1]['amount'] = 33;
		$groups[1]['is_automatic'] = true;
		$groups[2]['id'] = 3;
		$groups[2]['name'] = 'Popular groups';
		$groups[2]['image_path'] = 'popular_groups.jpg';
		$groups[2]['amount'] = 33;
		$groups[2]['is_automatic'] = true;
		return $response->withJson($groups);
	}
	public function getGroupClients (Request $request, Response $response, array $args):Response {
		return $response->withJson(rand(0,2) ? CustomersList::generateClients(rand(0, 30)) : []);
	}

	public function add (Request $request, Response $response):Response {
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
	public function rename (Request $request, Response $response) {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		if (!$this->validateGroupName($body['name'])) {
			return $response->withStatus(400)->write('name cannot be empty');
		}

		return $response->withStatus(204);
	}
	public function delete (Request $request, Response $response) {
		return $response->withStatus(204);
	}
	public function addClients (Request $request, Response $response) {
		$body = $request->getParsedBody();
		if (empty($body) || !$this->validateClientsList($body)) {
			return $response->withStatus(400)->write('clients malformed, has to be an array of integers: ' . json_encode($body));
		}

		return $response->withStatus(204);
	}

	private function generateGroup(): array {
		$group_name = Utils::generatePhrase();
		return [
			'id' => rand(4, 15),
			'name' => $group_name,
			'image_path' => str_replace(' ', '_', $group_name) . '.jpg',
			'amount' => rand(1, 30),
			'is_automatic' => false,
		];
	}

	private function checkBodyCorrectness($body) {
		$correct_body = ['name', 'clients'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff($correct_body, array_keys($body));
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg = implode(', ', $diff_keys) . ' argument should exist' . "<br>";
		}

		if (!$this->validateGroupName($body['name'])) { $is_correct = false; $msg .= 'name cannot be empty' . "<br>"; }
		$clients = json_decode($body['clients'] ?? null);
		if (!is_array($clients)) {
			$is_correct = false; $msg .= 'clients empty' . "<br>";
		} else {
			if (!$this->validateClientsList($clients)) {
				$is_correct = false; $msg .= "clients malformed, has to be an array of integers: $body[clients] <br>";
			}
		}

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function validateClientsList($clients) {
		return count(array_filter($clients, "is_int")) === count($clients);
	}
	private function validateGroupName($name) {
		return !empty($name);
	}
}
