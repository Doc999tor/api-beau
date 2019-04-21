<?php

namespace Lib\Controllers\Settings;
use Lib\Helpers\Utils;
use Lib\Controllers\Controller as Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SettingsCtrl extends Controller {
	public function getMapsAPIKey (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!isset($params['token']) || $params['token'] !== '2FR1LtzDxrAkL8oHDreybAtD') {
			$response->getBody()->write('incorrect request');
			return $response->withStatus(400);
		}

		$api_keys = include ($_SERVER['DOCUMENT_ROOT'] . '/api_keys.php');
		return $response
			->withJson(['api_key' => $api_keys['maps']])
			->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (86400 * 10)));
	}
	public function getBusinessData (Request $request, Response $response) {
		return $response
			->withJson([
				'new_clients_amonth' => rand(1,20),
				'new_clients_this_year' => rand(1,120),
				'growth_services_amonth' => rand(1,40),
				'growth_paid_amonth' => rand(1,2000),
			])
			->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (86400 * 10)));
	}
}