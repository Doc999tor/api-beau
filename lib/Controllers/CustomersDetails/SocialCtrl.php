<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SocialCtrl extends Controller {
	public function addSocial (Request $request, Response $response) {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$socials = ['facebook','instagram','linkedin','twitter','pinterest','google+','vk','website'];


		if (!isset($body['type']) or !in_array($body['type'], $socials)) {
			$response->getBody()->write('field "type" doesn\'t exists or includes non exists the enum value');
			return $response->withStatus(400);
		} else if (!isset($body['url']) or !preg_match("/^https?:\/\//i", $body['url'])) {
			$response->getBody()->write('field "url" doesn\'t exists or doesnt start with http/https');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(201);
		}
	}
	public function deleteSocial (Request $request, Response $response, array $args) {
		if ($request->getBody()->getSize()) {
			$response->getBody()->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}
}