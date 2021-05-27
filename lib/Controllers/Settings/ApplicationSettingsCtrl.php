<?php

namespace Lib\Controllers\Settings;
use Lib\Helpers\Utils;
use Lib\Controllers\Controller as Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ApplicationSettingsCtrl extends Controller {
	public function setApplicationLang (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['lang']) && in_array($body['lang'], ['he', 'en', 'ua', 'ru'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('lang supposed to be a correct language - two chars');
			return $response->withStatus(400);
		}
	}
	public function setApplicationCurrency (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['currency']) && in_array($body['currency'], ['nis', 'usd', 'eur', 'uah'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('currency supposed to be a correct currency - tree chars');
			return $response->withStatus(400);
		}
	}
	public function setApplicationTimezone (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['timezone']) && preg_match('/\w+\/\w+/', $body['timezone'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('timezone supposed to be a correct timezone - like Pacific/Auckland');
			return $response->withStatus(400);
		}
	}
}
