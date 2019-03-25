<?php

namespace Lib\Controllers;
use Lib\Helpers\Utils;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SettingsCtrl extends Controller {
	public function getMapsAPIKey (Request $request, Response $response) {
		// $body = $request->getParsedBody();
		$body = $response->getBody();
		$body->write('tjNy9K6JfYACeBYJF-cZshcrmGSLTA5dyamB73x');
		return $response->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (86400 * 10)));
	}
}