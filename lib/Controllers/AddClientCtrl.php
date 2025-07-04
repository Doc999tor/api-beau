<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AddClientCtrl extends Controller {
	public function index (Request $request, Response $response):Response {
		$static_prefix = str_repeat('../', substr_count($request->getUri()->getPath(), '/'));
		$base_path = $request->getUri()->getBasePath();

		return $this->view->render($response, 'add-client.html', [
			'base_path' => $base_path,
			'prefix' => $static_prefix,
		]);
	}

	public function getClients (Request $request, Response $response) {
		$params = $request->getQueryParams();

		if (!empty($params['phone'])) { # searching by phone
			if (!ctype_digit($params['phone'])) {
				$body = $response->getBody(); $body->write('phone query parameter has to be integer');
				return $response->withStatus(400);
			}
			if (rand(1,3) % 3 === 0) {
				$clients = $this->generateClients(10);
				foreach ($clients as &$client) {
					$client['phone'] = $params['phone'] . substr($client['phone'], strlen($params['phone']));
				}
			} else { $clients = []; }
			return $response->withJson($clients);
		}

		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		return $response->withJson($this->generateClients(50, $q));
	}
	public function getClient (Request $request, Response $response, $args) {
		$params = $request->getQueryParams();

		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if (!(int)$id) {
			return $response->withStatus(400);
		}

		return $response->withJson($this->generateClient('', $id, true));
	}

	public function generateClients(int $limit, string $q = '') {
		$clients = [];

		// Reducing $limit as length of $q rises
		switch (mb_strlen($q)) {
			case 0: $limit = rand(0, $limit); break;
			case 3: $limit = (time()%10 > 4) ? round(rand(0, $limit)) : 0; break;
			case 4: $limit = (time()%10 > 5) ? round(rand(0, $limit)) : 0; break;
			case 5: $limit = (time()%10 > 6) ? round(rand(0, $limit)) : 0; break;
			default: $limit = 0;
		}

		return CustomersList::generateClients($limit, $q);
	}

	public function addClient (Request $request, Response $response):Response {
		$files = $request->getUploadedFiles();

		if (!empty($files)) {
			$is_files_correct = $this->checkFilesCorrectness($files);
		} else {$is_files_correct = ["is_correct" => true, "msg" => ''];}

		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		if (isset($body['email']) && $body['email'] === 'exists@mail.com') {
			return $response->withStatus(409)->withJson([
				"error" => 'duplicate_email'
			]);
		}

		$is_body_correct = $this->checkBodyCorrectness($body);

		// if (isset($body['permit_ads']) && $body['permit_ads'] === 'true' && !isset($files['signature'])) {
		// 	$is_body_correct['is_correct'] = false;
		// 	$is_body_correct['msg'] .= 'if the user permits getting ads, he has to sign' . "<br>";
		// }

		if ($is_body_correct['is_correct'] && $is_files_correct['is_correct']) {
			$status = rand(0,3) ? 201 : 402;
			if ($status === 201) {
				$random_id = rand(50, 500);
				// $status_with_sms = rand(0,3) ? 201 : 409;
				$res = [
					'bonus_points' => null,
					'data' => [
						"id" => $random_id,
						"profile_image" => "{$random_id}.jpg",
						"phone" => '+38' . rand(1000000, 999999999),
					]
				];
				if (rand(0,3)) {
					$res['bonus_points'] = ['actions' => [['type' => 'earn_regular', 'points' => rand(1,10) * 10]]];
					if (rand(0,1)) {
						$res['bonus_points']['actions'] []= ['type' => 'complete_3_tasks', 'points' => rand(1,10) * 10];
					}
				}

				return $response->withStatus($status)->withJson($res);
			} else {
				$new_clients = rand(10,50);
				$max = round($new_clients * (rand(0,2) ? 1.1 : 1));
				$response_obj = [
					"used" => $new_clients,
					"max" => $max,
					"is_limit_reached" => $new_clients >= $max
				];

				return $response->withStatus($status)->withJson($response_obj);
			}
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg'] . "<br>" . $is_files_correct['msg']);
			return $response->withStatus(400);
		}
	}
	private function checkBodyCorrectness (array $body): array {
		$correct_body = ['name', 'phone', 'email', 'status', 'note', 'address', 'birthdate', 'birthyear', 'is_filling_up_sent', 'gender', 'permit_ads', 'debts', 'notes', 'social', 'source', 'tags', 'telegram', 'instagram', 'custom_fields', 'preferred_messenger', 'force', 'added'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # unexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if (isset($body['phone']) && $body['phone'] !== 'null' && !preg_match('/^[\d\s()+*#-]+$/', $body['phone'])) {
			$is_correct = false; $msg .= "phone number doesn't match the pattern - /^[\d\s()+*#-]+$/<br>";
		}
		if ($body['email'] !== 'null' && !preg_match('/^.*@.*\..{2,}$/', urldecode($body['email']))) { $is_correct = false; $msg .= 'email does\'nt match the pattern - /^.*@.*\..{2,}$/' . "<br>"; }
		if (isset($body['address']) && mb_strlen($body['address']) < 3) { $is_correct = false; $msg .= 'address too short' . "<br>"; }

		if (isset($body['birthdate']) && $body['birthdate'] !== 'null' && !\DateTime::createFromFormat('m-d', $body['birthdate'])) { $is_correct = false; $msg .= 'birthdate has to be MM-DD format, like 01-01' . "<br>"; }
		if (isset($body['birthyear']) && $body['birthyear'] !== 'null' && !\DateTime::createFromFormat('Y', $body['birthyear'])) { $is_correct = false; $msg .= 'birthyear has to be YYYY format, like 1970' . "<br>"; }

		if (isset($body['is_filling_up_sent']) && !in_array($body['is_filling_up_sent'], ['true', 'false'])) { $is_correct = false; $msg .= 'is_filling_up_sent can be true or false' . "<br>"; }
		if (isset($body['gender']) && !in_array($body['gender'], ['male', 'female', 'null'])) { $is_correct = false; $msg .= 'gender can be male or female' . "<br>"; }

		if (isset($body['debts'])) {
			$debts = json_decode($body['debts']);
			if (!is_array($debts) || count(array_filter($debts, function ($dept) {
				return isset($dept->sum) && is_int($dept->sum) && isset($dept->desc) && is_string($dept->desc);
			})) !== count($debts)) { $is_correct = false; $msg .= 'debts are malformed' . "<br>"; }
		}
		if (isset($body['notes'])) {
			$notes = json_decode($body['notes']);
			if (!$notes || count(array_filter($notes, function ($note) {
				if (!(isset($note->text) && is_string($note->text))) {
					return false;
				}
				if (isset($note->reminder_date) && !\DateTime::createFromFormat('Y-m-d H:i:s', $note->reminder_date)) {
					return false;
				}
				return true;
			})) !== count($notes)) { $is_correct = false; $msg .= "notes are malformed, check the note. reminder_date has to be YYYY-MM-DD hh:mm:ss format<br>"; }
		}

		if (isset($body['social'])) {
			$social_links = json_decode($body['social']);
			$types = ['facebook','instagram','linkedin','twitter','pinterest','google+','vk','website'];
			if (!$social_links || count(array_filter($social_links, function ($link) use ($types) {
				return isset($link->type) && in_array($link->type, $types) && isset($link->url) && $this->isValidUrl($link->url);
			})) !== count($social_links)) { $is_correct = false; $msg .= 'social_links are malformed' . "<br>"; }
		}

		if (empty($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $is_correct = false; $msg .= 'added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function checkFilesCorrectness(array $files): array {
		$correct_body = ['photo', 'sign'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($files), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		for ($i=0; $i < count($correct_body); $i++) {
			$file_name = $correct_body[$i];

			if (isset($files[$file_name])) {
				$file = $files[$file_name];
				if ($file->getSize() === 0) { $is_correct = false; $msg .= $file_name . ' came empty' . "<br>"; }
				if ($file->getSize() > \Lib\Helpers\Utils::returnBytes('10m')) { $is_correct = false; $msg .= $file_name . ' too big, more than 10mb' . "<br>"; }
				if (substr($file->getClientMediaType(), 0, 6) !== 'image/') { $is_correct = false; $msg .= $file_name . '\'s MIME type is incorrect' . "<br>"; }

				$extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
				$filename = preg_replace('/^php/', '', basename($file->file, '.tmp'));

				$file->moveTo('image' . DIRECTORY_SEPARATOR . "{$file_name}-{$filename}.{$extension}");
			}
		}

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function isValidUrl (string $url):bool { return preg_match('/^\w.*\..{2,}$/', $url); }

	public function getMedia (Request $request, Response $response):Response {
		$directory = '/image';
		$files = glob($_SERVER['DOCUMENT_ROOT'] . $directory . '/*.*');
		usort($files, function ($a, $b) {
			return filemtime($a) > filemtime($b) ? -1 : 1;
		});
		return $this->view->render($response, 'medialist.html', [
			"directory" => $directory,
			"files" => array_map(function ($file) {
				return [
					"name" => basename($file),
					"size" => round(filesize($file) / 1024),
					"dimensions" => getimagesize($file),
				];
			}, $files),
		]);
	}
	public function getSourceVariants (Request $request, Response $response):Response {
		$options = CustomersList::getSourceVariants();
		return $response->withJson($options);
	}
	public function getTagsVariants (Request $request, Response $response):Response {
		$options = CustomersList::getTagsVariants();
		return $response->withJson(
			array_map(function ($o) { return '#' . $o; }, $options)
		);
	}
}
