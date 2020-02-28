<?php

namespace Lib\Controllers\Settings;
use Lib\Helpers\Utils;
use Lib\Controllers\Controller as Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SmsSettingsCtrl extends Controller {

	public function fillCredits (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (isset($body['credits_requested_count']) && ctype_digit($body['credits_requested_count'])) {
			return $response->withStatus(201);
		} else {
			$response->getBody()->write('credits_requested_count has to exist and to be integer');
			return $response->withStatus(400);
		}
	}

	public function shouldSend (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if ($this->getListPredicate('should_send', $body, ['true', 'false'])) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write($this->getListErrorMessage('should_send', ['true', 'false']));
			return $response->withStatus(400);
		}
	}

	public function eventReminders (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (
			$this->getListPredicate('should_send', $body, ['true', 'false'])
			&& $this->getListPredicate('mins_before', $body, ['30', '60', ' 1440', '2880'])
		) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write($this->getListErrorMessage('should_send', ['true', 'false']) . '<br> and <br>' . $this->getListErrorMessage('mins_before', ['30', '60', ' 1440', '2880']));
			return $response->withStatus(400);
		}
	}

	public function greetingsBeforeBirthdays (Request $request, Response $response): Response {
		$body = $request->getParsedBody();
		if (
			$this->getListPredicate('should_send', $body, ['true', 'false'])
			&& $this->getListPredicate('days_before', $body, ['0', '1', '7'])
			&& (!empty($body['time_for_sending']) && \DateTime::createFromFormat('H:i', $body['time_for_sending']))
		) {
			return $response->withStatus(204);
		} else {
			$response->getBody()->write($this->getListErrorMessage('should_send', ['true', 'false']) . '<br> and <br>' . $this->getListErrorMessage('days_before', [0, 1, 7]) . '<br> and <br>time_for_sending supposed to be: hh:mm time');
			return $response->withStatus(400);
		}
	}

	private function getListPredicate ($paramName, $body, $list) {
		return isset($body[$paramName]) && in_array($body[$paramName], $list);
	}
	private function getListErrorMessage ($paramName, $list) {
		return $paramName . ' supposed to be: ' . implode(' | ', $list);
	}
}
