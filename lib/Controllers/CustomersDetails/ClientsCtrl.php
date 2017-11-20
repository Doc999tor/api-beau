<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends Controller {
	public function getClients(Request $request, Response $response) {
		return $response->getBody()->write('GET clients');
	}

	public function index (Request $request, Response $response):Response {
		$path = 'customers-details';
		$static_prefix = str_repeat('../', substr_count($request->getUri()->getPath(), '/'));
		$base_path = $request->getUri()->getBasePath();

		return $this->view->render($response, $path . '.html', [
			'base_path' => $base_path,
			'prefix' => $static_prefix,
			"path" => $path,
		]);
	}

	public function setPersonalData (Request $request, Response $response) {
		$body = $request->getParsedBody();

		$possible_keys = ['phone', 'email', 'birthdate', 'gender', 'isFavorite', 'address', 'status', 'source', 'permit_ads'];
		$keys = is_array($body) ? array_keys($body) : [];
		if (empty($keys)) {
			$response->getBody()->write('body is missing');
			return $response->withStatus(400);
		}
		if (!($request->getBody() && in_array($keys[0], $possible_keys))) {
			$response->getBody()->write('body is malformed');
			return $response->withStatus(400);
		}

		switch ($keys[0]) {
			case 'phone':
				if (!preg_match('/^((?![a-zA-Z]).)*$/', $body['phone'])) {
					$response->getBody()->write('phone value is incorrect');
					return $response->withStatus(400);
				}
				break;
			case 'email':
				if (!(strpos($body['email'], '@') > 0)) {
					$response->getBody()->write('email is incorrect');
					return $response->withStatus(400);
				}
				break;
			case 'birthdate':
				if (!\DateTime::createFromFormat('Y-m-d', $body['birthdate'])) {
					$response->getBody()->write('birthdate is incorrect, it has to be Y-m-d H:i format, like 1970-01-01');
					return $response->withStatus(400);
				}
				break;
			case 'gender':
				if (!in_array($body['gender'], ['male', 'female', 'null'])) {
					$response->getBody()->write('gender can be male or female');
					return $response->withStatus(400);
				}
				break;
			case 'isFavorite':
				if (!in_array($body['isFavorite'], ['true', 'false'])) {
					$response->getBody()->write('isFavorite value is incorrect');
					return $response->withStatus(400);
				}
				break;
			case 'status':
				if (mb_strlen($body['status']) < 2) {
					$response->getBody()->write('status value is too short');
					return $response->withStatus(400);
				}
				break;
			case 'address':
				if (mb_strlen($body['address']) < 4) {
					$response->getBody()->write('address is too short');
					return $response->withStatus(400);
				}
				break;
			case 'source':
				$source_options = ["ads", "fb_page", "family", "friends", "recommendation"];
				if (!in_array(strtolower($body['source']), $source_options)) {
					$response->getBody()->write('source is not from the list: ["ads", "fb_page", "family", "friends", "recommendation"]');
					return $response->withStatus(400);
				} else if ($body['source'] === 'recommendation' && !(isset($body['recommended_by']) && is_int((int)$body['recommended_by']) && (int)$body['recommended_by'] > 1)) {
					$response->getBody()->write('recommended_by should be a positive integer');
					return $response->withStatus(400);
				}
				break;
			case 'permit_ads':
				if (!in_array($body['permit_ads'], ['true', 'false'])) {
					$response->getBody()->write('permit_ads value is incorrect');
					return $response->withStatus(400);
				}
				break;
		}

		return $response->withStatus(204);
	}

	public function getMap (Request $request, Response $response) {
		$response->write(file_get_contents('public/map.jpg'));

		return $response
			->withHeader('Content-Type', 'image/jpeg')
			->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
	}

	public function sendLinkFillUpPersonalData (Request $request, Response $response) :Response {
		if ($request->getBody()->getSize()) {
			$response->getBody()->write('body should be empty');
			return $response->withStatus(400);
		}
		return $response->withStatus(204);
	}
}