<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class MediaCtrl extends Controller {
	public function addMedia (Request $request, Response $response) {
		$is_body_correct = self::checkMedia($request, 'file');

		if ($is_body_correct['is_correct']) {
			$uploaded_file = $request->getUploadedFiles()['file'];
			$uploaded_file_name = $uploaded_file->getClientFilename();
			$ext = pathinfo($uploaded_file_name)['extension'];

			$extension = pathinfo($uploaded_file->getClientFilename(), PATHINFO_EXTENSION);
			$filename = preg_replace('/^php/', '', basename($uploaded_file->file, '.tmp'));
			$uploaded_file->moveTo('image' . DIRECTORY_SEPARATOR . "{$uploaded_file_name}-{$filename}.{$extension}");

			return $response->withJson(["id" => rand(1, 150), "name" => bin2hex(random_bytes(4)) . ".{$ext}"])->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function editMediaNote (Request $request, Response $response):Response {
		$body = $request->getParsedBody();

		if (!isset($body['note'])) {
			$response->getBody()->write('note field does not exist');
			return $response->withStatus(400);
		}
		return $response->withStatus(204);
	}
	public function removeMedia (Request $request, Response $response):Response {
		return $response->withStatus(204);
	}

	public static function checkMedia(Request $request, $file_name) {
		$body = $request->getParsedBody();
		$files = $request->getUploadedFiles();

		$is_correct = true; $msg = '';
		if (!isset($files[$file_name])) {
			$is_correct = false; $msg .= $file_name . " is not sent or sent not under \"$file_name\" field<br>";
		} else if ((int)$request->getHeaderLine('Content-Length') > \Lib\Helpers\Utils::returnBytes(ini_get('post_max_size'))) {
			$is_correct = false; $msg .= 'file is too big, it should be under ' . ini_get("post_max_size") . "<br>";
		} else if (!isset($body['date']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['date'])) {
			$is_correct = false; $msg .= 'date has to be YYYY-MM-DD hh:mm:ss format, like  2017-12-18 02:09:54<br>';
		}

		$permitted_types = ['image/jpeg', 'image/png', 'image/webp', 'image/vnd.adobe.photoshop', 'application/pdf', 'application/ogg', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'text/plain', 'audio/aac', 'audio/amr', 'audio/ac3', 'audio/mp3', 'audio/mp4', 'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/wave', 'audio/webm', 'audio/x-pn-wav', 'audio/x-wav', 'video/mp4', 'video/avi', 'video/ogg', 'video/webm'];

		if (isset($files[$file_name]) && !in_array(mime_content_type($files[$file_name]->file), $permitted_types)) {
			 $is_correct = false; $msg .= 'MIME type is not supported';
		}
		return ["is_correct" => $is_correct, "msg" => $msg];
	}
}