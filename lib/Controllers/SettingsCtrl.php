<?php

namespace Lib\Controllers;
use Lib\Helpers\Utils;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SettingsCtrl extends Controller {
	public function getMapsAPIKey (Request $request, Response $response) {
		// $body = $request->getParsedBody();
		return $response
			->withJson(['api_key' => 'tjNy9K6JfYACeBYJF-cZshcrmGSLTA5dyamB73x'])
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

	public function setCalendarView(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if ($body['key']) {
			return $response->withStatus(204);
		} else {
			$response = $response->getBody()->write('body supposed to be: agenda | daily | weekly | monthly');
			return $response->withStatus(400);
		}
	}
	public function setViewStartsOn(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if ($body['key']) {
			return $response->withStatus(204);
		} else {
			$response = $response->getBody()->write('body supposed to be: 0-6 ');
			return $response->withStatus(400);
		}
	}
	public function setShowCalendarFrom(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if ($body['key']) {
			return $response->withStatus(204);
		} else {
			$response = $response->getBody()->write('body supposed to be: hh:mm time');
			return $response->withStatus(400);
		}
	}
	public function setShowCalendarTo(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if ($body['key']) {
			return $response->withStatus(204);
		} else {
			$response = $response->getBody()->write('body supposed to be: hh:mm time');
			return $response->withStatus(400);
		}
	}
	public function setSlotDuration(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if ($body['key']) {
			return $response->withStatus(204);
		} else {
			$response = $response->getBody()->write('body supposed to be: 5 | 10 | 15 | 20 | 30 | 60');
			return $response->withStatus(400);
		}
	}
	public function setAllowMultipleEventsOnTheSameTimeSlot(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if ($body['key']) {
			return $response->withStatus(204);
		} else {
			$response = $response->getBody()->write('body supposed to be: true | false');
			return $response->withStatus(400);
		}
	}
	public function setAllowSchedulingOutsideOfTimeSlots(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if ($body['key']) {
			return $response->withStatus(204);
		} else {
			$response = $response->getBody()->write('body supposed to be: true | false');
			return $response->withStatus(400);
		}
	}
}