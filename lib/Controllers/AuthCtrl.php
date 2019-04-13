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
	public function countries (Request $request, Response $response):Response {
		return $response->withJson([
			"country" => "IL",
			"timezone" => "Asia/Jerusalem",
			"city" => "Tel Aviv"
		]);
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

	public function getBusinessTypes(Request $request, Response $response) {
		$body = $response->getBody();
		$body->write('[{"id":1,"name":"Beauty and aesthetics","icon":"beauty_and_aesthetics.svg","ordering":1},{"id":2,"name":"Therapists","icon":"therapists.svg","ordering":2},{"id":3,"name":"Coaches and teachers","icon":"coaches_and_teachers.svg","ordering":3},{"id":4,"name":"Repairman and transporters aesthetics","icon":"repairman_and_transporters_aesthetics.svg","ordering":4},{"id":5,"name":"One-on-one meetings","icon":"one-on-one_meetings.svg","ordering":5},{"id":6,"name":"Other","icon":"other.svg","ordering":6},{"id":7,"name":"Holistic therapy","icon":"holistic_therapy.svg","ordering":7},{"id":8,"name":"Electricians","icon":"electricians.svg","ordering":8},{"id":9,"name":"Private teachers","icon":"private_teachers.svg","ordering":9},{"id":10,"name":"Plumbing","icon":"plumbing.svg","ordering":10},{"id":11,"name":"Tattoo parlors","icon":"tattoo_parlors.svg","ordering":11},{"id":12,"name":"Naturopaths","icon":"naturopaths.svg","ordering":12},{"id":13,"name":"Сoaches","icon":"сoaches.svg","ordering":13},{"id":14,"name":"Nutritionists","icon":"nutritionists.svg","ordering":14},{"id":15,"name":"Pest control","icon":"pest_control.svg","ordering":15},{"id":16,"name":"Сounseling","icon":"сounseling.svg","ordering":16},{"id":17,"name":"Sales agents","icon":"sales_agents.svg","ordering":17},{"id":18,"name":"Movers","icon":"movers.svg","ordering":18},{"id":19,"name":"Therapists","icon":"therapists.svg","ordering":19},{"id":20,"name":"Wedding Salon","icon":"wedding_salon.svg","ordering":20},{"id":21,"name":"Hairdressers","icon":"hairdressers.svg","ordering":21},{"id":22,"name":"Architects","icon":"architects.svg","ordering":22},{"id":23,"name":"Beauticians","icon":"beauticians.svg","ordering":23},{"id":24,"name":"Manicure","icon":"manicure.svg","ordering":24}]');
		return $response->withHeader('Content-type', 'application/json');
	}
}