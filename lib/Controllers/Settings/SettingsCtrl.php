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
	public function getGoogleContactsAPIKey (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!isset($params['token']) || $params['token'] !== 'VsQuDWNd94u6csau3X3uTtx7') {
			$response->getBody()->write('incorrect request');
			return $response->withStatus(400);
		}

		$api_keys = include ($_SERVER['DOCUMENT_ROOT'] . '/api_keys.php');
		return $response
			->withJson(['api_key' => $api_keys['google-calendar']])
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
	public function signLegal (Request $request, Response $response) {
		$body = $request->getParsedBody();
		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) {
			$response_body = $response->getBody();
			$response_body->write('added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54 <br>');
			return $response->withStatus(400);
		}
		return $response->withStatus(201);
	}

	public function setAccountInfo (Request $request, Response $response) {
		$body = json_decode($request->getBody()->getContents(), true);
		$is_body_correct = $this->checkAccountBodyCorrectness($body['business_info']);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkAccountBodyCorrectness($body) {
		$correct_body = ['name', 'category', 'phone', 'address'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff($correct_body, array_keys($body));
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg = implode(', ', $diff_keys) . ' argument should exist';
		}

		if (isset($body['name']) && ($body['name'] !== null && mb_strlen($body['name']) < 2)) { $is_correct = false; $msg .= 'name cannot be empty' . "<br>"; }
		if (isset($body['category']) && ($body['category'] !== null && !intval($body['category']))) { $is_correct = false; $msg .= 'category is not valid' . "<br>"; }
		if (isset($body['phone']) && ($body['phone'] !== null && !$this->isClientPhoneValid($body['phone']))) { $is_correct = false; $msg .= 'phone is not valid' . "<br>"; }
		if (isset($body['address']) && ($body['address'] !== null && mb_strlen($body['address']) < 2)) { $is_correct = false; $msg .= 'address cannot be empty' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	public function setBillingDetails (Request $request, Response $response) {
		$body = json_decode($request->getBody()->getContents(), true);
		$is_body_correct = $this->checkBillingBodyCorrectness($body);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkBillingBodyCorrectness($body) {
		$correct_body = ['name', 'address', 'email', 'accountant_email'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff($correct_body, array_keys($body));
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg = implode(', ', $diff_keys) . ' argument should exist';
		}

		if (isset($body['name']) && ($body['name'] !== null && mb_strlen($body['name']) < 2)) { $is_correct = false; $msg .= 'name cannot be empty' . "<br>"; }
		if (isset($body['address']) && ($body['address'] !== null && mb_strlen($body['address']) < 2)) { $is_correct = false; $msg .= 'address cannot be empty' . "<br>"; }
		if (isset($body['email']) && ($body['email'] !== null && strpos($body['email'], '@') === false)) { $is_correct = false; $msg .= 'email is not valid' . "<br>"; }
		if (isset($body['accountant_email']) && ($body['accountant_email'] !== null && strpos($body['accountant_email'], '@') === false)) { $is_correct = false; $msg .= 'accountant_email is not valid' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	public function setNotifications (Request $request, Response $response) {
		$body = json_decode($request->getBody()->getContents(), true);
		$is_body_correct = $this->checkNotificationsBodyCorrectness($body);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkNotificationsBodyCorrectness($body) {
		$correct_body = ['new_event', 'reschedule_event', 'delete_event', 'reminders_before_event', 'greetings_before_birthdays', 'automatic_filling_up_sending', ];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff($correct_body, array_keys($body));
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg = implode(', ', $diff_keys) . ' argument should exist';
		}

		if (!isset($body['new_event']) || gettype($body['new_event']) !== 'array') { $is_correct = false; $msg .= 'new_event cannot be empty' . "<br>";
		} else {
			$should_send = $body['new_event']['should_send'];
			if (!isset($should_send) || gettype($should_send) !== 'boolean') { $is_correct = false; $msg .= 'new_event.should_send is not valid' . "<br>"; }
		}
		if (!isset($body['reschedule_event']) || gettype($body['reschedule_event']) !== 'array') { $is_correct = false; $msg .= 'reschedule_event cannot be empty' . "<br>";
		} else {
			$should_send = $body['reschedule_event']['should_send'];
			if (!isset($should_send) || gettype($should_send) !== 'boolean') { $is_correct = false; $msg .= 'reschedule_event.should_send is not valid' . "<br>"; }
		}
		if (!isset($body['delete_event']) || gettype($body['delete_event']) !== 'array') { $is_correct = false; $msg .= 'delete_event cannot be empty' . "<br>";
		} else {
			$should_send = $body['delete_event']['should_send'];
			if (!isset($should_send) || gettype($should_send) !== 'boolean') { $is_correct = false; $msg .= 'delete_event.should_send is not valid' . "<br>"; }
		}
		if (!isset($body['reminders_before_event']) || gettype($body['reminders_before_event']) !== 'array') { $is_correct = false; $msg .= 'reminders_before_event cannot be empty' . "<br>";
		} else {
			$should_send = $body['reminders_before_event']['should_send'];
			if (!isset($should_send) || gettype($should_send) !== 'boolean') { $is_correct = false; $msg .= 'reminders_before_event.should_send is not valid' . "<br>"; }
			$mins_before = $body['reminders_before_event']['mins_before'];
			if (!isset($mins_before) || gettype($mins_before) !== 'integer') { $is_correct = false; $msg .= 'reminders_before_event.mins_before is not valid' . "<br>"; }
		}
		if (!isset($body['greetings_before_birthdays']) || gettype($body['greetings_before_birthdays']) !== 'array') { $is_correct = false; $msg .= 'greetings_before_birthdays cannot be empty' . "<br>";
		} else {
			$should_send = $body['greetings_before_birthdays']['should_send'];
			if (!isset($should_send) || gettype($should_send) !== 'boolean') { $is_correct = false; $msg .= 'greetings_before_birthdays.should_send is not valid' . "<br>"; }
			$days_before = $body['greetings_before_birthdays']['days_before'];
			if (!isset($days_before) || gettype($days_before) !== 'integer') { $is_correct = false; $msg .= 'greetings_before_birthdays.days_before is not valid' . "<br>"; }
			$time_for_sending = $body['greetings_before_birthdays']['time_for_sending'];
			if (!isset($time_for_sending) || !\DateTime::createFromFormat('H:i', $time_for_sending)) { $is_correct = false; $msg .= 'greetings_before_birthdays.time_for_sending is not valid' . "<br>"; }
		}
		if (!isset($body['automatic_filling_up_sending']) || gettype($body['automatic_filling_up_sending']) !== 'array') { $is_correct = false; $msg .= 'automatic_filling_up_sending cannot be empty' . "<br>";
		} else {
			$should_send = $body['automatic_filling_up_sending']['should_send'];
			if (!isset($should_send) || gettype($should_send) !== 'boolean') { $is_correct = false; $msg .= 'automatic_filling_up_sending.should_send is not valid' . "<br>"; }
		}

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function isClientPhoneValid(/*string */$phone_string): bool {
		return !preg_match('/[^\d\s()+*#-]/', $phone_string);
	}
}
