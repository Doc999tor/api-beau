<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends Controller {
	public function getClients(Request $request, Response $response) {
		return $response->getBody()->write('GET clients');
	}

	public function setPersonalData (Request $request, Response $response) {
		$body = $request->getParsedBody();

		$possible_keys = ['phone', 'email', 'vip', 'address', 'status'];
		$keys = is_array($body) ? array_keys($body) : [];
		if (!($request->getBody() && count($keys) === 1 && in_array($keys[0], $possible_keys))) {
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
			case 'vip':
				if (!in_array($body['vip'], ['true', 'false'])) {
					$response->getBody()->write('vip value is incorrect');
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
		}

		return $response->withStatus(204);
	}

	public function getMap (Request $request, Response $response) {
	    $response->write(file_get_contents('public/map.jpg'));

	    return $response
	    	->withHeader('Content-Type', 'image/jpg')
	    	->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)))
				->withHeader('Content-Length', strlen($map));
	}

	public function addMedia (Request $request, Response $response) {
		$details = $request->getParsedBody();
		$file = $request->getUploadedFiles()['file'];

		$permitted_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/ogg', 'audio/aac', 'audio/mp4', 'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/wave', 'audio/webm', 'audio/x-pn-wav', 'audio/x-wav', 'video/mp4', 'video/avi', 'video/ogg', 'video/webm'];

		if (!in_array($file->getClientMediaType(), $permitted_types)) {
			$response->getBody()->write('MIME type is not supported');
			return $response->withStatus(400);
		} else if (!isset($details['description'])) {
			$response->getBody()->write('description field does not exist');
			return $response->withStatus(400);
		}
		return $response->withStatus(201);
	}
}