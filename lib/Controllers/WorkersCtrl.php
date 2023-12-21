<?php

namespace Lib\Controllers;

use \Lib\Controllers\Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Lib\Helpers\Utils;

class WorkersCtrl extends Controller {
	public function getAllWorkersData (Request $request, Response $response, array $args):Response {
		$workers = [];
		for ($i=1; $i < rand(2, 6); ++$i) {
			$workers []= $this->generateWorker($i);
		}

		return $response->withJson($workers);
	}
	public function getData (Request $request, Response $response, array $args):Response {
		return $response->withJson($this->generateWorker(filter_var($args['worker_id'], FILTER_SANITIZE_NUMBER_INT)));
	}

	private function generateWorker(int $id) {
		$faker = \Faker\Factory::create();

		$worker = [
			'id' => $id,
			'name' => $faker->firstName,
			'is_open_online' => (bool) rand(0, 1),
		];

		$weekend = rand(0,6);
		$short_day = rand(0,6);
		for ($i=0; $i < 7; $i++) {
			if ($i === $weekend) { continue; }
			$worker['day_' . $i] = [
				'start' => '10:00',
				'end' => '18:00',
			];
			if ($i === $short_day) {
				$worker['day_' . $i]['end'] = '14:00';
			}
		}

		return $worker;
	}

	public function addWorker (Request $request, Response $response):Response {
		$files = $request->getUploadedFiles();

		if (!empty($files)) {
			$is_files_correct = $this->checkFilesCorrectness($files);
		} else {$is_files_correct = ["is_correct" => true, "msg" => ''];}

		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct'] && $is_files_correct['is_correct']) {
			$random_id = rand(50, 500);
			$response_body = array_merge(
				["id" => $random_id, "permission_level" => "staff"],
				array_map(function ($v) {
					$decoded_value = json_decode($v, true);
					return json_last_error() === JSON_ERROR_NONE ? $decoded_value : $v;
				}, $body)
			);
			if (!empty($files)) {
				$response_body['photo'] = "{$random_id}.jpg";
			}
			return $response->withStatus(rand(0,3) ? 201 : 409)->withJson($response_body);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg'] . "<br>" . $is_files_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function updateWorker (Request $request, Response $response, $args):Response {
		$files = $request->getUploadedFiles();

		if (!empty($files)) {
			$is_files_correct = $this->checkFilesCorrectness($files);
		} else {$is_files_correct = ["is_correct" => true, "msg" => ''];}

		$body = $this->parsePutBody($request->getBody()->getContents());
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct'] && $is_files_correct['is_correct']) {
			$random_id = rand(50, 500);
			$response_body = array_merge(
				["id" => $args['worker_id'], "permission_level" => "staff"],
				array_map(function ($v) {
					$decoded_value = json_decode($v, true);
					return json_last_error() === JSON_ERROR_NONE ? $decoded_value : $v;
				}, $body)
			);
			if (!empty($body['photo'])) {
				$response_body['photo'] = "{$random_id}.jpg";
			}
			return $response->withJson($response_body);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg'] . "<br>" . $is_files_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function updateWorkerDetail (Request $request, Response $response, $args):Response {
		$body = json_decode($request->getBody()->getContents(), true);
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkWorkerDetailCorrectness($body);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	public function deleteWorker (Request $request, Response $response, array $args):Response {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		}
		return $response->withStatus(204);
	}

	private function checkBodyCorrectness (array $body): array {
		$correct_body = ['name', 'phone', 'email', 'password', 'color', 'businessHours', 'added', 'photo', 'email_appointments_notifications'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if (empty($body['name']) || mb_strlen($body['name']) < 3) { $is_correct = false; $msg .= 'name too short' . "<br>"; }
		if (empty($body['phone']) || $body['phone'] !== 'null' && !preg_match('/^[\d\s()+*#-]+$/', $body['phone'])) {
			$is_correct = false; $msg .= "phone number doesn't match the pattern - /^[\d\s()+*#-]+$/<br>";
		}
		if (empty($body['email']) || !preg_match('/^.*@.*\..{2,}$/', urldecode($body['email']))) { $is_correct = false; $msg .= 'email does\'nt match the pattern - /^.*@.*\..{2,}$/' . "<br>"; }
		if (empty($body['password']) || mb_strlen($body['password']) < 3) { $is_correct = false; $msg .= 'password too short' . "<br>"; }
		if (isset($body['color']) && mb_strlen($body['color']) < 3) { $is_correct = false; $msg .= 'color too short' . "<br>"; }

		if (empty($body['businessHours'])) { $is_correct = false; $msg .= 'businessHours is missing' . "<br>"; }
		else {
			$businessHours = json_decode($body['businessHours'], true);
			if (count($businessHours) !== count(array_filter($businessHours, function ($day) {
				return isset($day['daysOfWeek'])
						&& is_array($day['daysOfWeek'])
						&& count($day['daysOfWeek']) === 1
						&& is_int($day['daysOfWeek'][0])
					&& isset($day['startTime']) && strlen($day['startTime']) === 5
					&& isset($day['endTime']) && strlen($day['endTime']) === 5;
			}))) { $is_correct = false; $msg .= 'businessHours incorrect' . "<br>"; }
		}

		if (empty($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $is_correct = false; $msg .= 'added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function checkWorkerDetailCorrectness (array $body): array {
		$correct_body = ['is_open_online'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}
		if (!count(array_keys($body))) { $is_correct = false; $msg .= 'body can\'t be empty' . "<br>"; }

		if (isset($body['is_open_online']) && !is_bool($body['is_open_online'])) { $is_correct = false; $msg .= 'is_open_online has to be boolean' . "<br>"; }
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
				if ($file->getSize() > Utils::returnBytes('10m')) { $is_correct = false; $msg .= $file_name . ' too big, more than 10mb' . "<br>"; }
				if (substr($file->getClientMediaType(), 0, 6) !== 'image/') { $is_correct = false; $msg .= $file_name . '\'s MIME type is incorrect' . "<br>"; }

				$extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
				$filename = preg_replace('/^php/', '', basename($file->file, '.tmp'));

				$file->moveTo('image' . DIRECTORY_SEPARATOR . "{$file_name}-{$filename}.{$extension}");
			}
		}

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function parsePutBody ($raw_data) {
		$boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

		// Fetch each part
		$parts = array_slice(explode($boundary, $raw_data), 1);
		$data = array();

		foreach ($parts as $part) {
		    // If this is the last part, break
		    if ($part == "--\r\n") break;

		    // Separate content from headers
		    $part = ltrim($part, "\r\n");
		    list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

		    // Parse the headers list
		    $raw_headers = explode("\r\n", $raw_headers);
		    $headers = array();
		    foreach ($raw_headers as $header) {
		        list($name, $value) = explode(':', $header);
		        $headers[strtolower($name)] = ltrim($value, ' ');
		    }

		    // Parse the Content-Disposition to get the field name, etc.
		    if (isset($headers['content-disposition'])) {
		        $filename = null;
		        preg_match(
		            '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
		            $headers['content-disposition'],
		            $matches
		        );
		        list(, $type, $name) = $matches;
		        isset($matches[4]) and $filename = $matches[4];

		        // handle your fields here
		        switch ($name) {
		            // this is a file upload
		            case 'userfile':
		                 file_put_contents($filename, $body);
		                 break;

		            // default for all other files is to populate $data
		            default:
		                 $data[$name] = substr($body, 0, strlen($body) - 2);
		                 break;
		        }
		    }
		}
		return $data;
	}
}
