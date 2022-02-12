<?php

namespace Lib\Controllers\Settings;

use \Lib\Controllers\Controller;
use \Lib\Controllers\ServicesCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Lib\Helpers\Utils;

class DigitalBusinessCardCtrl extends Controller {
	public function addCard (Request $request, Response $response):Response {
		$files = $request->getUploadedFiles();

		if (!empty($files)) {
			$is_files_correct = $this->checkFilesCorrectness($files);
		} else {$is_files_correct = ["is_correct" => true, "msg" => ''];}

		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct'] && $is_files_correct['is_correct']) {
			$random_id = rand(50, 500);
			$response_body = $body;
			$response_body['id'] = $random_id;
			if (isset($files['cover'])) {
				$filename = pathinfo($files['cover']->getClientFilename(),  PATHINFO_FILENAME);
				$response_body['cover'] = "{$filename}_{$random_id}";
			}
			if (isset($files['logo'])) {
				$filename = pathinfo($files['logo']->getClientFilename(),  PATHINFO_FILENAME);
				$response_body['logo'] = "{$filename}_{$random_id}";
			}
			return $response->withStatus(rand(0,3) ? 201 : 409)->withJson($response_body);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg'] . "<br>" . $is_files_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function updateCard (Request $request, Response $response, $args):Response {
		$files = $request->getUploadedFiles();

		$body = $this->parsePutBody($request->getBody()->getContents());
		$body = is_array($body) ? $body : [];
		var_dump($_FILES, $body);
		unset($body['logo']);
		unset($body['gallery']);
		unset($body['gallery[]']);
		unset($body['cover']);

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct']) {
			// $response_body = $body;
			// $response_body['id'] = $args['card_id'];
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg'] . "<br>" . $is_files_correct['msg']);
			return $response->withStatus(400);
		}
	}

	public function deleteCard (Request $request, Response $response, array $args):Response {
		if ($request->getBody()->getSize()) {
			$body = $response->getBody();
			$body->write('body has to be empty');
			return $response->withStatus(400);
		}
		return $response->withStatus(204);
	}

	private function checkBodyCorrectness (array $body): array {
		$correct_body = ['business_type_id', 'profession_name', 'business_name', 'business_description', 'phone', 'address', 'instagram', 'facebook', 'telegram', 'added'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if (empty($body['business_name']) || mb_strlen($body['business_name']) < 3) { $is_correct = false; $msg .= 'business_name too short' . "<br>"; }
		if (isset($body['business_description']) && mb_strlen($body['business_description']) < 3) { $is_correct = false; $msg .= 'business_description too short' . "<br>"; }
		if (empty($body['phone']) || $body['phone'] !== 'null' && !preg_match('/^[\d\s()+*#-]+$/', $body['phone'])) {
			$is_correct = false; $msg .= "phone number doesn't match the pattern - /^[\d\s()+*#-]+$/<br>";
		}
		if (isset($body['profession_name']) && mb_strlen($body['profession_name']) < 3) { $is_correct = false; $msg .= 'profession_name too short' . "<br>"; }
		if (isset($body['address']) && mb_strlen($body['address']) < 3) { $is_correct = false; $msg .= 'address too short' . "<br>"; }
		if (isset($body['telegram']) && mb_strlen($body['telegram']) < 3) { $is_correct = false; $msg .= 'telegram too short' . "<br>"; }
		if (isset($body['instagram']) && mb_strlen($body['instagram']) < 3) { $is_correct = false; $msg .= 'instagram too short' . "<br>"; }
		if (isset($body['facebook']) && mb_strlen($body['facebook']) < 3) { $is_correct = false; $msg .= 'facebook too short' . "<br>"; }


		if (empty($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $is_correct = false; $msg .= 'added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>'; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}
	private function checkFilesCorrectness(array $files): array {
		$correct_body = ['logo', 'cover'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($files), array_merge($correct_body, ['gallery'])); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		for ($i=0; $i < count($correct_body); ++$i) {
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

		if (!empty($files['gallery'])) {
			for ($i=0; $i < count($files['gallery']); ++$i) {
				$file = $files['gallery'][$i];

				if (isset($file)) {
					if ($file->getSize() === 0) { $is_correct = false; $msg .= "gallery[{$i}]" . ' came empty' . "<br>"; }
					if ($file->getSize() > \Lib\Helpers\Utils::returnBytes('10m')) { $is_correct = false; $msg .= "gallery[{$i}]" . ' too big, more than 10mb' . "<br>"; }
					if (substr($file->getClientMediaType(), 0, 6) !== 'image/') { $is_correct = false; $msg .= "gallery[{$i}]" . '\'s MIME type is incorrect' . "<br>"; }

					$extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
					$filename = preg_replace('/^php/', '', basename($file->file, '.tmp'));

					$file->moveTo('image' . DIRECTORY_SEPARATOR . "{$filename}.{$extension}");
				}
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
