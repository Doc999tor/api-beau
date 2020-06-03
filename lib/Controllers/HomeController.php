<?php

namespace Lib\Controllers;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class HomeController extends Controller {
	public function contact_us (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		$msg = ''; $is_correct = true;

		if (empty($body['contact_detail']) || !mb_strlen(trim($body['contact_detail']))) { $msg .= '<br> contact_detail has to be one letter at least'; $is_correct = false; }
		if (empty($body['message']) || !mb_strlen(trim($body['message']))) { $msg .= '<br> message has to be one letter at least'; $is_correct = false; }
		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $msg .= '<br>added has to be YYYY-MM-DD hh:mm:ss format, like  2019-12-18 02:09:54<br>'; $is_correct = false; }

		if (!$is_correct) {
			$body = $response->getBody();
			$body->write($msg);
			return $response->withStatus(400);
		} else { return $response->withStatus(201); }
	}
	public function contact_us_leads (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		$msg = ''; $is_correct = true;

		if (empty($body['contact_detail']) || !mb_strlen(trim($body['contact_detail']))) { $msg .= '<br> contact_detail has to be one letter at least'; $is_correct = false; }
		if (empty($body['name']) || !mb_strlen(trim($body['name']))) { $msg .= '<br> name has to be one letter at least'; $is_correct = false; }
		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $msg .= '<br>added has to be YYYY-MM-DD hh:mm:ss format, like  2019-12-18 02:09:54<br>'; $is_correct = false; }

		if (!$is_correct) {
			$body = $response->getBody();
			$body->write($msg);
			return $response->withStatus(400);
		} else { return $response->withStatus(201); }
	}
}
