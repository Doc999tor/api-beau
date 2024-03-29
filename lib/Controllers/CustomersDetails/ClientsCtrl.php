<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends Controller {
	public function getClients(Request $request, Response $response) {
		return $response->getBody()->write('GET clients');
	}

	public function index (Request $request, Response $response):Response {
		$path = 'customers-details';
		$static_prefix = str_repeat('../', substr_count($request->getUri()->getPath(), '/'));
		$base_path = $request->getUri()->getBasePath();

		return $this->view->render($response, $path . '.html', [
			'base_path' => $base_path,
			'prefix' => $static_prefix,
			"path" => $path,
		]);
	}

	public function removeUser (Request $request, Response $response) {
		if ($request->getBody()->getSize()) {
			$response->getBody()->write('body should be empty');
			return $response->withStatus(400);
		}
		return $response->withStatus(204);
	}

	public function getPersonalData (Request $request, Response $response, array $args) {
		$generated_client = \Lib\Controllers\CustomersList::generateClient();
		$generated_client['id'] = $args['client_id'];
		$generated_client['phone'] = [$generated_client['phone']];
		return $response->withJson($generated_client);
	}
	public function setPersonalData (Request $request, Response $response) {
		// PUT with form-data not working with php
		// $body = $request->getParsedBody();

		// $is_body_correct = $this->checkFillingUpData($body);
		// if ($is_body_correct['is_correct']) {
		// 	$random_id = rand(50, 500);
		// 	return $response->withJson(["profile_image" => "{$random_id}.jpg", ]);
		// } else {
		// 	$body = $response->getBody();
		// 	$body->write("<br>" . $is_body_correct['msg'] . "<br>");
		// 	return $response->withStatus(400);
		// }

		$random_id = rand(50, 500);
		$client = ["phone" => '+38' . rand(1000000, 999999999), "profile_image" => "{$random_id}.jpg", ];
		if (rand(0,2)) {
			$client['telegram'] = 'doc999tor';
		}
		if (rand(0,2)) {
			$client['instagram'] = 'javascript.js';
		}

		return $response->withJson($client);
	}
	public function setRTPersonalData (Request $request, Response $response) {
		$body = json_decode($request->getBody()->getContents(), true);

		$is_body_correct = $this->checkFillingUpData($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg'] . "<br>");
			return $response->withStatus(400);
		}
	}

	public function setProfileImage (Request $request, Response $response):Response {
		$params = $request->getQueryParams();
		$body = $request->getParsedBody();

		$media_manager = new MediaCtrl($this->container);

		return $media_manager->addMedia($request, $response);
	}

	public function getMap (Request $request, Response $response) {
		$response->write(file_get_contents('public/map.jpg'));

		return $response
			->withHeader('Content-Type', 'image/jpeg')
			->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
	}

	public function setPersonalDataFromClient (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		$is_body_correct = ['is_correct' => true, 'msg' => ''];
		if (!isset($body['b']) || !ctype_digit($body['b'])) {
			$is_body_correct['is_correct'] = false;
			$is_body_correct['msg'] = 'b has to be an integer';
		} else if (!isset($body['c']) || !ctype_alnum($body['c'])) {
			$is_body_correct['is_correct'] = false;
			$is_body_correct['msg'] = 'c has to be an alphanumeric';
		} else {
			$is_body_correct = $this->checkFillingUpData(array_diff_key($body, array_flip(['b', 'c'])));
		}

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function setProfileImageFromClient (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		$is_body_correct = ['is_correct' => true, 'msg' => ''];
		$is_body_correct['msg'] = $this->checkClientAuthData(
			array_intersect_key($body, array_flip(['b', 'c']))
		);
		if ($is_body_correct['msg']) {
			$is_body_correct['is_correct'] = false;
		} else {
			$is_body_correct = MediaCtrl::checkMedia($request, 'photo');
		}

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function addNoteFromClient (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		$is_body_correct = ['is_correct' => true, 'msg' => ''];
		$is_body_correct['msg'] = $this->checkClientAuthData(
			array_intersect_key($body, array_flip(['b', 'c']))
		);
		if ($is_body_correct['msg']) {
			$is_body_correct['is_correct'] = false;
		} else {
			$error_msg = NotesCtrl::checkCorrectnessBody(array_diff_key($body, array_flip(['b', 'c'])));
			$is_body_correct['is_correct'] = !$error_msg;
			$is_body_correct['msg'] = $error_msg;
		}

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	public function sendFillingUpLink (Request $request, Response $response): Response {
		return $response->withStatus(rand(0,3) ? 204 : 409);
	}

	public function unsubscribe (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		$is_body_correct = ['is_correct' => true, 'msg' => ''];
		if (!isset($body['b']) || !ctype_digit($body['b'])) {
			$is_body_correct['is_correct'] = false;
			$is_body_correct['msg'] .= 'b has to be an integer';
		} else if (!isset($body['c']) || !ctype_alnum($body['c'])) {
			$is_body_correct['is_correct'] = false;
			$is_body_correct['msg'] .= 'c has to be an alphanumeric';
		} else if (!isset($body['phone']) || !$this->isClientPhoneValid($body['phone'])) {
			$is_body_correct['is_correct'] = false;
			$is_body_correct['msg'] .= ' phone value is incorrect <br>';
		} else if (isset($body['text']) && empty($body['text'])) {
			$is_body_correct['is_correct'] = false;
			$is_body_correct['msg'] .= 'text cannot be empty <br>';
		}

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkFillingUpData ($body) {
		$possible_keys = ['fb_data', 'name', 'phone', 'email', 'birthyear', 'birthdate', 'gender', 'isFavorite', 'address', 'note', 'source', 'permit_ads', 'is_open_online'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($body), $possible_keys); # nonexpected fields exist
		if (!empty($diff_keys)) { $is_correct = false; $msg .= implode('<br>', $diff_keys) . ' argument' . (count($diff_keys) > 1 ? 's' : '') . ' should not exist<br>'; }

		foreach ($body as &$val) {
			if ($val === 'null') { $val = null; }
		}

		if (isset($body['phone']) && $body['phone'] !== 'null' && !$this->isClientPhoneValid($body['phone'])) {
			$is_correct = false; $msg .= "phone number doesn't match the pattern - /^[\d\s()+*#-]+$/<br>";
		}

		if (!empty($body['email']) && strpos($body['email'], '@') === false) { $is_correct = false; $msg .= ' email is incorrect <br>';}
		if (!empty($body['birthdate']) && !\DateTime::createFromFormat('m-d', $body['birthdate'])) { $is_correct = false; $msg .= ' birthdate is incorrect, it has to be m-d format, like ' . (new \DateTime())->format('m-d') . ' or null <br>';}
		if (!empty($body['birthyear']) && !\DateTime::createFromFormat('Y', $body['birthyear'])) { $is_correct = false; $msg .= ' birthyear is incorrect, it has to be YYYY format, like ' . (new \DateTime())->format('YYYY') . ' or null <br>';}
		if (!empty($body['gender']) && !in_array($body['gender'], ['male', 'female'])) { $is_correct = false; $msg .= ' gender can be null, male or female <br>';}
		if (!empty($body['isFavorite']) && !in_array($body['isFavorite'], ['true', 'false'])) { $is_correct = false; $msg .= ' isFavorite value is incorrect <br>';}
		if (!empty($body['note']) && mb_strlen($body['note']) < 2) { $is_correct = false; $msg .= ' note value is too short <br>';}
		if (!empty($body['address']) && mb_strlen($body['address']) < 4) { $is_correct = false; $msg .= ' address is too short <br>';}

		if (!empty($body['source'])) {
			$source_options = ["ads", "fb_page", "family", "friends", "recommendation"];
			if (!in_array(strtolower($body['source']), $source_options)) { $is_correct = false; $msg .= ' source is not from the list: [ <br>' . implode(',', $source_options) . ']';}
		}
		if (!empty($body['permit_ads']) && !in_array($body['permit_ads'], ['true', 'false'])) { $is_correct = false; $msg .= ' permit_ads value can be true or false <br>';}

		if (isset($body['is_open_online']) && !is_bool($body['is_open_online'])) { $is_correct = false; $msg .= ' is_open_online value can be true or false <br>';}

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function checkClientAuthData(array $authData):string {
		$msg = '';
		if (!isset($authData['b']) || !ctype_digit($authData['b'])) {
			$msg = 'b has to be an integer';
		} else if (!isset($authData['c']) || !ctype_alnum($authData['c'])) {
			$msg = 'c has to be an alphanumeric';
		}
		return $msg;
	}
	private function isClientPhoneValid(/*string */$phone_string): bool {
		return !preg_match('/[^\d\s()+*#-]/', $phone_string);
	}
}
