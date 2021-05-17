<?php

namespace Lib\Controllers;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AquaPlantsCtrl extends Controller {
	// https://api.bewebmaster.co.il/metrics/installation_popup_calendar
	public function order (Request $request, Response $response):Response {
		$rawBody = $request->getBody()->getContents();
		$body = json_decode($rawBody, true);

		$is_body_correct = $this->checkOrder($body);
		if (empty($rawBody) || !is_array($body)) { $is_body_correct['msg'] .= '<br> body is empty or is not correct'; $is_body_correct['is_correct'] = false; }

		if (!$is_body_correct['is_correct']) {
			$rawBody = $response->getBody();
			$rawBody->write($is_body_correct['msg']);
			return $response->withStatus(400);
		} else { return $response->withStatus(201); }
	}

	private function checkOrder(array $order) {
		// var_dump($order);
		$possible_keys = ['first_name', 'last_name', 'phone', 'email', 'address', 'city', 'total_price', 'order'];
		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($order), $possible_keys); # nonexpected fields exist
		if (!empty($diff_keys)) { $is_correct = false; $msg .= implode('<br>', $diff_keys) . ' argument' . (count($diff_keys) > 1 ? 's' : '') . ' should not exist<br>'; }

		foreach ($order as &$val) {
			if ($val === 'null') { $val = null; }
		}

		if (empty($order['phone']) || !$this->isClientPhoneValid($order['phone'])) {
			$is_correct = false; $msg .= "phone number doesn't match the pattern - /^[\d\s()+*#-]+$/<br>";
		}

		if (empty($order['email']) || strpos($order['email'], '@') === false) { $is_correct = false; $msg .= ' email is incorrect <br>';}
		if (empty($order['address']) || mb_strlen($order['address']) < 4) { $is_correct = false; $msg .= ' address is too short <br>';}
		if (empty($order['city']) || mb_strlen($order['city']) < 2) { $is_correct = false; $msg .= ' city is too short <br>';}
		if (!isset($order['total_price'])) { $is_correct = false; $msg .= ' total_price is too short <br>';}

		if (empty($order['order']) || count(array_filter($order['order'], [$this, 'isPlantValid'])) !== count($order['order'])) { $is_correct = false; $msg .= ' order is incorrect <br>';}

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function isClientPhoneValid(/*string */$phone_string): bool {
		return (bool) preg_match('/[^\d\s()+*#-]+$/', $phone_string);
	}
	private function isPlantValid($plant): bool {
		return !empty($plant['id']) && !empty($plant['qty']) && !empty($plant['price']);
	}
}
