<?php

namespace Lib\Controllers\Settings;
use Lib\Helpers\Utils;
use Lib\Controllers\Controller as Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CalendarSettingsCtrl extends Controller {
	public function setCalendarView(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['calendar_view']) && in_array($body['calendar_view'], ['agenda', 'daily', 'weekly', 'monthly'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('calendar_view supposed to be: agenda | daily | weekly | monthly');
			return $response->withStatus(400);
		}
	}
	public function setViewStartsOn(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		$digit = (int) $body['view_starts_on'];
		if (isset($body['view_starts_on']) && $digit >= 0 && $digit < 7) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('view_starts_on supposed to be: 0-6 ');
			return $response->withStatus(400);
		}
	}
	public function setShowCalendarFrom(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['show_calendar_from']) && \DateTime::createFromFormat('H:i', $body['show_calendar_from'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('show_calendar_from supposed to be: hh:mm time');
			return $response->withStatus(400);
		}
	}
	public function setShowCalendarTo(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['show_calendar_to']) && \DateTime::createFromFormat('H:i', $body['show_calendar_to'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('show_calendar_to supposed to be: hh:mm time');
			return $response->withStatus(400);
		}
	}
	public function setSlotDuration(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['slotDuration']) && in_array($body['slotDuration'], ['5', '10', '15', '20', '30', '60'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('slotDuration supposed to be: 5 | 10 | 15 | 20 | 30 | 60');
			return $response->withStatus(400);
		}
	}
	public function setAllowMultipleEventsOnTheSameTimeSlot(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['allow_multiple_events_on_the_same_time_slot']) && in_array($body['allow_multiple_events_on_the_same_time_slot'], ['true', 'false'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('allow_multiple_events_on_the_same_time_slot supposed to be: true | false');
			return $response->withStatus(400);
		}
	}
	public function setAllowSchedulingOutsideOfTimeSlots(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['allow_scheduling_outside_of_time_slots']) && in_array($body['allow_scheduling_outside_of_time_slots'], ['true', 'false'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('allow_scheduling_outside_of_time_slots supposed to be: true | false');
			return $response->withStatus(400);
		}
	}
	public function setIsIncomeShown(Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['is_income_shown']) && in_array($body['is_income_shown'], ['true', 'false'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write('is_income_shown supposed to be: true | false');
			return $response->withStatus(400);
		}
	}
}
