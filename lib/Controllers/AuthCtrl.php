<?php

namespace Lib\Controllers;
use Lib\Helpers\Utils;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthCtrl extends Controller {
	public function signup (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		$is_body_correct = $this->checkSignupDataCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	private function checkSignupDataCorrectness (array $body): array {
		$correct_body = ['email', 'pass', 'permit_ads', 'business_types', 'lang', 'timezone', 'added'];
		$is_correct = true; $msg = '';

		if (empty($body['email']) || strpos($body['email'], '@') === false) { $is_correct = false; $msg .= ' email value is incorrect <br>'; }
		if (empty($body['pass'])) { $is_correct = false; $msg .= ' pass value is incorrect <br>'; }
		if (empty($body['lang']) || strlen($body['lang']) !== 2) { $is_correct = false; $msg .= ' lang value is incorrect <br>'; }
		if (empty($body['timezone']) || strpos($body['timezone'], '/') === false) { $is_correct = false; $msg .= ' timezone value is incorrect <br>'; }

		if (isset($body['permit_ads']) && !in_array($body['permit_ads'], ['true', 'false'])) { $is_correct = false; $msg .= ' permit_ads value is incorrect <br>';}
		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $is_correct = false; $msg .= ' added has to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18 02:09:54 <br>'; }

		$types = isset($body['business_types']) ? json_decode($body['business_types']) : null;
		if (!is_array($types) || count(array_filter($types, function ($type) {
			return is_int($type);
		})) !== count($types)) { $is_correct = false; $msg .= 'business_types are malformed' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
}