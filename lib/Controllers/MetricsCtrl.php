<?php

namespace Lib\Controllers;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class MetricsCtrl extends Controller {
	// https://api.bewebmaster.co.il/metrics/installation_popup_calendar
	public function addCalendarInstallationMetrics (Request $request, Response $response):Response {
		$rawBody = $request->getBody()->getContents();

		$msg = ''; $is_correct = true;
		if (empty($rawBody) || !is_array(json_decode(trim($rawBody), true)) || !count(json_decode(trim($rawBody), true))) { $msg .= '<br> body is empty or is not correct'; $is_correct = false; }

		if (!$is_correct) {
			$rawBody = $response->getBody();
			$rawBody->write($msg);
			return $response->withStatus(400);
		} else { return $response->withStatus(201); }
	}
	public function addMessagingSmsMetrics (Request $request, Response $response):Response {
		return $response->withStatus(201);
	}
	public function addMessagingWhatsappMetrics (Request $request, Response $response):Response {
		return $response->withStatus(201);
	}
}
