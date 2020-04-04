<?php

namespace Lib\Controllers;
use Lib\Helpers\Utils;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthCtrl extends Controller {
	public function checkSighup (Request $request, Response $response):Response {
		$req_body = $request->getParsedBody();
		['is_correct' => $is_correct, 'msg' => $msg, 'error_code' => $error_code, ] = $this->validateBasicCreds($req_body);

		if ($is_correct) {
			$error_code = $this->checkExistingCreds($req_body['email'], $req_body['pass']);
		} else {
			$error_code = 400;
			$body = $response->getBody();
			$body->write($msg);
		}
		return $response->withStatus($error_code);
	}

	public function signup (Request $request, Response $response):Response {
		$req_body = $request->getParsedBody();

		$is_body_correct = $this->checkSignupDataCorrectness($req_body);
		if ($is_body_correct['is_correct']) {
			$body = $response->getBody();
			$body->write("/{$req_body['lang']}/calendar");
			return $response->withStatus($is_body_correct['error_code'] === 404 ? 201 : $is_body_correct['error_code']);
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
	private function checkSignupDataCorrectness (?array $body): array {
		$correct_body = ['email', 'pass', 'phone', 'permit_ads', 'business_types', 'lang', 'timezone', 'added'];
		[ 'is_correct' => $is_correct, 'msg' => $msg, 'error_code' => $error_code, ] = $this->validateBasicCreds($body);

		# checking existing email and pass
		if ($is_correct) {
			$error_code = $this->checkExistingCreds($body['email'], $body['pass']);
		}

		if (empty($body['lang']) || strlen($body['lang']) !== 2) { $is_correct = false; $msg .= ' lang value is incorrect <br>'; }
		if (empty($body['timezone']) || strpos($body['timezone'], '/') === false) { $is_correct = false; $msg .= ' timezone value is incorrect <br>'; }

		if (isset($body['permit_ads']) && !in_array($body['permit_ads'], ['true', 'false'])) { $is_correct = false; $msg .= ' permit_ads value is incorrect <br>';}
		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $is_correct = false; $msg .= ' added has to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18 02:09:54 <br>'; }

		$types = isset($body['business_types']) ? json_decode($body['business_types']) : null;
		if (!is_array($types) || count(array_filter($types, 'is_int')) !== count($types)) { $is_correct = false; $msg .= 'business_types are malformed' . "<br>"; }

		return ['is_correct' => $is_correct, 'error_code' => $error_code, 'msg' => $msg];
	}

	private function validateBasicCreds($body): array {
		$is_correct = true; $msg = ''; $error_code = $is_correct ? 200 : 400;
		if (empty($body['email']) || strpos($body['email'], '@') === false) { $is_correct = false; $msg .= " email {$body['email']} value is incorrect <br>"; }
		if (empty($body['pass'])) { $is_correct = false; $msg .= " pass {$body['pass']} value is incorrect <br>"; }
		return [ "is_correct" => $is_correct, "msg" => $msg, 'error_code' => $error_code, ];
	}

	private function checkExistingCreds(string $email, string $pass): int {
		if ($email === 'exists@mail.com' || !rand(0,5)) {
			if ($pass === 'existing_pass' || !rand(0,3)) {
				$error_code = 302; # found
			} else {
				$error_code = 409; # email exists, pass doesn't
			}
		} else {
			$error_code = 404; # uknown email
		}
		return $error_code;
	}

	public function getBusinessTypes(Request $request, Response $response) {
		$body = $response->getBody();
		$body->write('[{"id":1,"name":"Beauty and aesthetics","icon":"beauty_and_aesthetics.svg","ordering":1},{"id":2,"name":"Therapists","icon":"therapists.svg","ordering":2},{"id":3,"name":"Coaches and teachers","icon":"coaches_and_teachers.svg","ordering":3},{"id":4,"name":"Repairman and transporters aesthetics","icon":"repairman_and_transporters_aesthetics.svg","ordering":4},{"id":5,"name":"One-on-one meetings","icon":"one-on-one_meetings.svg","ordering":5},{"id":6,"name":"Holistic therapy","icon":"holistic_therapy.svg","ordering":7},{"id":7,"name":"Electricians","icon":"electricians.svg","ordering":8},{"id":8,"name":"Private teachers","icon":"private_teachers.svg","ordering":9},{"id":9,"name":"Plumbing","icon":"plumbing.svg","ordering":10},{"id":10,"name":"Tattoo parlors","icon":"tattoo_parlors.svg","ordering":11},{"id":11,"name":"Naturopaths","icon":"naturopaths.svg","ordering":12},{"id":12,"name":"Сoaches","icon":"сoaches.svg","ordering":13},{"id":13,"name":"Nutritionists","icon":"nutritionists.svg","ordering":14},{"id":14,"name":"Pest control","icon":"pest_control.svg","ordering":15},{"id":15,"name":"Сounseling","icon":"сounseling.svg","ordering":16},{"id":16,"name":"Sales agents","icon":"sales_agents.svg","ordering":17},{"id":17,"name":"Movers","icon":"movers.svg","ordering":18},{"id":18,"name":"Therapists","icon":"therapists.svg","ordering":19},{"id":19,"name":"Wedding Salon","icon":"wedding_salon.svg","ordering":20},{"id":20,"name":"Hairdressers","icon":"hairdressers.svg","ordering":21},{"id":21,"name":"Architects","icon":"architects.svg","ordering":22},{"id":22,"name":"Beauticians","icon":"beauticians.svg","ordering":23},{"id":23,"name":"Manicure","icon":"manicure.svg","ordering":24},{"id":-1,"name":"Other","icon":"other.svg","ordering":6}]');
		return $response->withHeader('Content-type', 'application/json');
	}
}
