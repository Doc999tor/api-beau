<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class MediaCtrl extends Controller {
	public function addMedia (Request $request, Response $response) {
		$body = $request->getParsedBody();
		$files = $request->getUploadedFiles();

		if (!isset($files['file'])) {
			$response->getBody()->write('file is not sent or sent not under "file" field');
			return $response->withStatus(400);
		}

		$file = $files['file'];

		$permitted_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/ogg', 'audio/aac', 'audio/mp4', 'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/wave', 'audio/webm', 'audio/x-pn-wav', 'audio/x-wav', 'video/mp4', 'video/avi', 'video/ogg', 'video/webm'];

		if (!in_array($file->getClientMediaType(), $permitted_types)) {
			$response->getBody()->write('MIME type is not supported');
			return $response->withStatus(400);
		} else if (!isset($body['note'])) {
			$response->getBody()->write('note field does not exist');
			return $response->withStatus(400);
		}
		return $response->withStatus(201);
	}
	public function editMediaNote (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		if (!isset($body['note'])) {
			$response->getBody()->write('note field does not exist');
			return $response->withStatus(400);
		}
		return $response->withStatus(204);
	}
	public function removeMedia (Request $request, Response $response):Response {
		return $response->withStatus(204);
	}
}