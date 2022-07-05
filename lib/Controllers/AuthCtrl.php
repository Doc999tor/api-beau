<?php

namespace Lib\Controllers;
use Lib\Helpers\Utils;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthCtrl extends Controller {
	public function checkLogin (Request $request, Response $response):Response {
		$req_body = json_decode($request->getBody()->getContents(), true);

		$error_code = $this->checkExistingCreds($req_body['email'], $req_body['pass']);
		return $response->withStatus($error_code);
	}

	public function checkSignup (Request $request, Response $response):Response {
		$req_body = $request->getParsedBody();
		if (is_array($req_body) && count($req_body) < 2) {
			$raw_body = $request->getBody()->getContents();
			$req_body = json_decode($raw_body, true);
		}

		$base_creds_validation = $this->validateBasicCreds($req_body);
		if (!$base_creds_validation['is_correct']) {
			$body = $response->getBody();
			$body->write($base_creds_validation['msg']);
			return $response->withStatus($base_creds_validation['error_code']);
		}

		$client_details_validation = $this->validateClientDetails($req_body);
		if (!$client_details_validation['is_correct']) {
			$body = $response->getBody();
			$body->write($client_details_validation['msg']);
			return $response->withStatus($client_details_validation['error_code']);
		}

		$error_code = $this->checkNonExistingCreds($req_body['email'], $req_body['pass']);
		return $response->withStatus($error_code);
	}

	public function signup (Request $request, Response $response):Response {
		$req_body = $request->getParsedBody();

		$is_body_correct = $this->checkSignupDataCorrectness($req_body);
		if ($is_body_correct['is_correct']) {
			$body = $response->getBody();
			$body->write("/{$req_body['lang']}/calendar");

			switch ($is_body_correct['error_code']) {
				case 404: $error_code = 201; break; # 404 means it's a unrecognised email
				case 409:
				case 302: $error_code = 422; break;
				default: $error_code = $is_body_correct['error_code'];
			}

			return $response->withStatus($error_code);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	public function resetPassword (Request $request, Response $response):Response {
		$req_body = $request->getParsedBody();
		if (!empty($req_body['email'])) {
			return $response->withStatus($req_body['email'] === 'non_exists@mail.com' ? 404 : 201);
		} else {
			$body = $response->getBody();
			$body->write('email is not valid');
			return $response->withStatus(400);
		}
	}
	public function setPassword (Request $request, Response $response):Response {
		$req_body = $request->getParsedBody();

		$is_body_correct = $this->checkSetPasswordCorrectness($req_body);
		if ($is_body_correct['is_correct']) {
			return $response;
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
		$is_correct = true; $msg = '';
		if (empty($body['email']) || strpos($body['email'], '@') === false) { $is_correct = false; $msg .= " email {$body['email']} value is incorrect <br>"; }
		if (empty($body['pass']) || mb_strlen(trim($body['pass'])) <= 3) { $is_correct = false; $msg .= " pass {$body['pass']} value is incorrect or less than 4 chars<br>"; }
		$error_code = $is_correct ? 200 : 400;
		return [ "is_correct" => $is_correct, "msg" => $msg, 'error_code' => $error_code, ];
	}
	private function validateClientDetails($body): array {
		$is_correct = true; $msg = '';
		if (empty($body['name']) || mb_strlen($body['name']) < 4) { $is_correct = false; $msg .= " name value is incorrect or less than 4 chars<br>"; }
		if (empty($body['phone']) || !preg_match('/^[\d\s()+*#-]+$/', $body['phone'])) {
			$is_correct = false; $msg .= "phone number doesn't match the pattern - /^[\d\s()+*#-]+$/<br>";
		}
		$error_code = $is_correct ? 200 : 400;
		return [ "is_correct" => $is_correct, "msg" => $msg, 'error_code' => $error_code, ];
	}
	private function checkSetPasswordCorrectness($body): array {
		$is_correct = true; $msg = '';
		if (empty($body['current-password']) || strlen($body['current-password']) <= 3) { $is_correct = false; $msg .= " current-password value is too short <br>"; }
		if (empty($body['rid']) || mb_strlen(trim($body['rid'])) <= 3) { $is_correct = false; $msg .= " rid value is incorrect or less than 4 chars<br>"; }
		return [ "is_correct" => $is_correct, "msg" => $msg];
	}

	private function checkExistingCreds(string $email, string $pass): int {
		$error_code = 404; # unknown email

		if ($email === 'exists@mail.com') {
			if ($pass === 'existing_pass') {
				$error_code = 201; # found
			} else {
				// $error_code = 409; # email exists, pass doesn't
			}
		}

		return $error_code;
	}
	private function checkNonExistingCreds(string $email, string $pass): int {
		$error_code = 201; # unknown email

		if ($email === 'exists@mail.com') {
			if ($pass === 'existing_pass') {
				$error_code = 409; # found
			} else {
				// $error_code = 409; # email exists, pass doesn't
			}
		}

		return $error_code;
	}

	public function getBusinessTypes(Request $request, Response $response) {
		$body = $response->getBody();
		$body->write('[{"id":1,"name":"Beauty and aesthetics","icon":"beauty_and_aesthetics","ordering":1},{"id":2,"name":"Therapists","icon":"therapists","ordering":2},{"id":3,"name":"Coaches and teachers","icon":"coaches_and_teachers","ordering":3},{"id":4,"name":"Repairman and transporters aesthetics","icon":"repairman_and_transporters_aesthetics","ordering":4},{"id":5,"name":"One-on-one meetings","icon":"one-on-one_meetings","ordering":5},{"id":6,"name":"Holistic therapy","icon":"holistic_therapy","ordering":7},{"id":7,"name":"Electricians","icon":"electricians","ordering":8},{"id":8,"name":"Private teachers","icon":"private_teachers","ordering":9},{"id":9,"name":"Plumbing","icon":"plumbing","ordering":10},{"id":10,"name":"Tattoo parlors","icon":"tattoo_parlors","ordering":11},{"id":11,"name":"Naturopaths","icon":"naturopaths","ordering":12},{"id":12,"name":"Сoaches","icon":"сoaches","ordering":13},{"id":13,"name":"Nutritionists","icon":"nutritionists","ordering":14},{"id":14,"name":"Pest control","icon":"pest_control","ordering":15},{"id":15,"name":"Сounseling","icon":"сounseling","ordering":16},{"id":16,"name":"Sales agents","icon":"sales_agents","ordering":17},{"id":17,"name":"Movers","icon":"movers","ordering":18},{"id":18,"name":"Therapists","icon":"therapists","ordering":19},{"id":19,"name":"Wedding Salon","icon":"wedding_salon","ordering":20},{"id":20,"name":"Hairdressers","icon":"hairdressers","ordering":21},{"id":21,"name":"Architects","icon":"architects","ordering":22},{"id":22,"name":"Beauticians","icon":"beauticians","ordering":23},{"id":23,"name":"Manicure","icon":"manicure","ordering":24},{"id":-1,"name":"Other","icon":"other","ordering":6}]');
		return $response->withHeader('Content-type', 'application/json');
	}
}
