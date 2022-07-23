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

		foreach ($body as $key => &$value) {
			if ($value === 'null') {
				$value = null;
			}
		}

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct'] && $is_files_correct['is_correct']) {
			$random_id = rand(50, 500);
			$response_body = $body;
			$response_body['id'] = $random_id;
			$response_body['slug'] = $this->clearBusinessName($response_body['business_name']);

			if (isset($files['cover'])) {
				$filename = pathinfo($files['cover']->getClientFilename(), PATHINFO_FILENAME);
				$extension = pathinfo($files['cover']->getClientFilename(), PATHINFO_EXTENSION);
				$response_body['cover'] = "{$filename}_{$random_id}.{$extension}";
			} else {
				$response_body['cover'] = null;
			}
			if (isset($files['logo'])) {
				$filename = pathinfo($files['logo']->getClientFilename(), PATHINFO_FILENAME);
				$extension = pathinfo($files['logo']->getClientFilename(), PATHINFO_EXTENSION);
				$response_body['logo'] = "{$filename}_{$random_id}.{$extension}";
			} else {
				$response_body['logo'] = null;
			}
			if (isset($files['gallery'])) {
				$pics = [];
				for ($i=0; $i < count($files['gallery']); ++$i) {
					$file = $files['gallery'][$i];

					if (isset($file)) {
						$filename = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
						$extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
						$pics []= "{$filename}_{$random_id}.{$extension}";
					}
				}
				$response_body['gallery'] = $pics;
			} else {
				$response_body['gallery'] = null;
			}
			if (isset($files['gallery[]'])) {
				$pics = [];
				for ($i=0; $i < count($files['gallery[]']); ++$i) {
					$file = $files['gallery[]'][$i];

					if (isset($file)) {
						$filename = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
						$extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
						$pics []= "{$filename}_{$random_id}.{$extension}";
					}
				}
				$response_body['gallery'] = $pics;
			} else {
				$response_body['gallery'] = null;
			}
			return $response->withStatus(rand(0,3) ? 201 : 409)->withJson($response_body);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg'] . "<br>" . $is_files_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function updateCard (Request $request, Response $response, $args):Response {
		$body = $this->parsePutBody($request->getBody()->getContents());
		$body = is_array($body) ? $body : [];

		// var_dump($body);
		$logo = $body['logo'];
		unset($body['logo']);
		$gallery = $body['gallery'] ?? $body['gallery[]'];
		unset($body['gallery']);
		$cover = $body['cover'];
		unset($body['cover']);

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct']) {
			$response_body = $body;
			$response_body['id'] = $args['card_id'];

			$random_id = $args['card_id'];
			$response_body = $body;
			$response_body['id'] = $random_id;
			$response_body['slug'] = $this->clearBusinessName($response_body['business_name']);

			if (isset($cover) && $cover !== 'null') {
				$filename = pathinfo($cover, PATHINFO_FILENAME);
				$extension = pathinfo($cover, PATHINFO_EXTENSION);
				$response_body['cover'] = "{$filename}_{$random_id}.{$extension}";
			} else {
				$response_body['cover'] = null;
			}
			if (isset($logo) && $logo !== 'null') {
				$filename = pathinfo($logo, PATHINFO_FILENAME);
				$extension = pathinfo($logo, PATHINFO_EXTENSION);
				$response_body['logo'] = "{$filename}_{$random_id}.{$extension}";
			} else {
				$response_body['logo'] = null;
			}
			if (isset($gallery)) {
				$pics = [];
				for ($i=0; $i < count($gallery); ++$i) {
					$file = $gallery[$i];

					if (isset($file)) {
						$filename = pathinfo($file, PATHINFO_FILENAME);
						$extension = pathinfo($file, PATHINFO_EXTENSION);
						$pics []= "{$filename}_{$random_id}.{$extension}";
					}
				}
				$response_body['gallery'] = $pics;
			} else {
				$response_body['gallery'] = null;
			}

			return $response->withJson($response_body);
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

	public function getQR (Request $request, Response $response) {
		$response->write(file_get_contents('public/qr.jpg'));

		return $response
			->withHeader('Content-Type', 'image/jpeg')
			->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
	}

	private function checkBodyCorrectness (array $body): array {
		$correct_body = ['business_type_id', 'profession_name', 'is_personal_cabinet_enabled', 'business_name', 'business_description', 'phone', 'address', 'instagram', 'facebook', 'telegram', 'viber', 'slug', 'added', 'logo', 'cover', 'gallery', 'gallery[]'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if (empty($body['business_name']) || mb_strlen($body['business_name']) < 3) { $is_correct = false; $msg .= 'business_name too short' . "<br>"; }
		if (!empty($body['business_description']) && mb_strlen($body['business_description']) < 3) { $is_correct = false; $msg .= 'business_description too short' . "<br>"; }
		if (!empty($body['phone']) && $body['phone'] !== 'null' && !preg_match('/^[\d\s()+*#-]+$/', $body['phone'])) {
			$is_correct = false; $msg .= "phone number doesn't match the pattern - /^[\d\s()+*#-]+$/<br>";
		}
		if (empty($body['is_personal_cabinet_enabled']) || !in_array($body['is_personal_cabinet_enabled'], ['true', 'false'])) { $is_correct = false; $msg .= 'is_personal_cabinet_enabled has to be boolean' . "<br>"; }
		if (!empty($body['profession_name']) && mb_strlen($body['profession_name']) < 3) { $is_correct = false; $msg .= 'profession_name too short' . "<br>"; }
		if (!empty($body['address']) && mb_strlen($body['address']) < 3) { $is_correct = false; $msg .= 'address too short' . "<br>"; }
		if (!empty($body['telegram']) && mb_strlen($body['telegram']) < 3) { $is_correct = false; $msg .= 'telegram too short' . "<br>"; }
		if (!empty($body['viber']) && !in_array($body['viber'], ['true', 'false'])) { $is_correct = false; $msg .= 'viber is not correct' . "<br>"; }
		if (!empty($body['instagram']) && mb_strlen($body['instagram']) < 3) { $is_correct = false; $msg .= 'instagram too short' . "<br>"; }
		if (!empty($body['facebook']) && mb_strlen($body['facebook']) < 3) { $is_correct = false; $msg .= 'facebook too short' . "<br>"; }


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
		$data = ['gallery' => []];

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
					case 'gallery':
					case 'gallery[]':
						$data['gallery'] []= $filename;
						break;

					// default for all other files is to populate $data
					default:
						if ($filename === 'null') {
							$data[$name] = null;
						} else if (!empty($filename)) {
							$data[$name] = $filename;
						} else {
							$val = substr($body, 0, strlen($body) - 2);
							$data[$name] = $val === 'null'
								? null
								: $val;
						}
						break;
				}
			}
		}
		return $data;
	}

	private function clearBusinessName($business_name): string {
		return trim(preg_replace('/[:;%#$^&.,\\|+\s]+/', '-', $business_name), '-');
	}


	public function updateOnlineBooking (Request $request, Response $response, $args):Response {
		$body = json_decode($request->getBody()->getContents(), true);
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkOnlineBookingCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(204);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	private function checkOnlineBookingCorrectness (array $body): array {
		$correct_body = ['is_online_booking_enabled'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if (!isset($body['is_online_booking_enabled']) || !is_bool($body['is_online_booking_enabled'])) { $is_correct = false; $msg .= 'is_online_booking_enabled has to be boolean' . "<br>"; }
		return ["is_correct" => $is_correct, "msg" => $msg];
	}
}
