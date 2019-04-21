<?php

namespace Lib\Controllers\Settings;
use Lib\Helpers\Utils;
use Lib\Controllers\Controller as Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CalendarSettingsCtrl extends Controller {
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