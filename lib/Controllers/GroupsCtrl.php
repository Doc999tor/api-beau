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
		$groups[1]['id'] = 2;
		$groups[1]['name'] = 'Have not been a while clients';
		$groups[1]['image_path'] = 'have_not_been_a_while_clients.jpg';
		$groups[1]['amount'] = 33;
		$groups[2]['id'] = 3;
		$groups[2]['name'] = 'Popular groups';
		$groups[2]['image_path'] = 'popular_groups.jpg';
		$groups[2]['amount'] = 33;
		return $response->withJson($groups);
	}
	public function getGroupClients (Request $request, Response $response, array $args):Response {
		return $response->withJson(CustomersList::generateClients(mt_rand(0, 30)));
	}

	private function generateGroup(): array {
		$group_name = Utils::generatePhrase();
		return [
			'id' => rand(4, 15),
			'name' => $group_name,
			'image_path' => str_replace(' ', '_', $group_name) . '.jpg',
			'amount' => rand(1, 30),
		];
	}
}