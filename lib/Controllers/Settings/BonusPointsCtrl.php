<?php

namespace Lib\Controllers\Settings;

use \Lib\Controllers\Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class BonusPointsCtrl extends Controller {
	public function earn (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_correct = true; $msg = '';
		if (empty($body['bonus']) || $body['bonus'] !== 'daily') {
			$is_correct = false; $msg .= " bonus has to be 'daily'<br>";
		}

		if ($is_correct) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $msg);
			return $response->withStatus(400);
		}
	}
	public function redeem (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_correct = true; $msg = '';
		if (empty($body['bonus'])) {
			$is_correct = false; $msg .= " bonus has to be non-empty string<br>";
		}

		if ($is_correct) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $msg);
			return $response->withStatus(400);
		}
	}
}
