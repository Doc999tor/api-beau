<?php

namespace Lib\Controllers\Settings;
use Lib\Helpers\Utils;
use Lib\Controllers\Controller as Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ApplicationSettingsCtrl extends Controller {
	public function setApplicationLang (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['application_lang']) && in_array($body['application_lang'], ['he', 'en', 'ua', 'ru'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('application_lang supposed to be a correct language - two chars');
			return $response->withStatus(400);
		}
	}
	public function setApplicationCurrency (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['application_currency']) && in_array($body['application_currency'], ['nis', 'usd', 'eur', 'uah'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('application_currency supposed to be a correct currency - tree chars');
			return $response->withStatus(400);
		}
	}
	public function setApplicationTimezone (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['application_timezone']) && preg_match('/\w+\/\w+/', $body['application_timezone'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('application_timezone supposed to be a correct timezone - like Pacific/Auckland');
			return $response->withStatus(400);
		}
	}
}