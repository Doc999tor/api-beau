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
			['filename' => $filename, 'extension' => $extension] = pathinfo($uploaded_file_name);

			$hash = preg_replace('/^php/', '', basename($uploaded_file->file, '.tmp'));
			$filename = "{$filename}-{$hash}.{$extension}";
			$uploaded_file->moveTo('image' . DIRECTORY_SEPARATOR . $filename);

			return $response->withJson(["id" => rand(1, 150), "name" => $filename])->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus($is_body_correct['status']);
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

		$is_correct = true; $http_status = 200; $msg = '';
		if (!isset($files[$file_name])) {
			$is_correct = false; $http_status = 400; $msg .= $file_name . " is not sent or sent not under \"$file_name\" field<br>";
		} else if ((int)$request->getHeaderLine('Content-Length') > \Lib\Helpers\Utils::returnBytes('8M' /*ini_get('post_max_size')*/)) { # files larger than post_max_size just stripped from the request
			$is_correct = false; $http_status = 413; $msg .= 'file is too big, it should be under ' . '8M' /*ini_get("post_max_size")*/ . "<br>";
		} else if (isset($body['date']) && !\DateTime::createFromFormat('Y-m-d H:i:s', $body['date'])) {
			$is_correct = false; $http_status = 400; $msg .= 'date has to be YYYY-MM-DD hh:mm:ss format, like 2021-01-01 02:09:54<br>';
		}

		$permitted_types = ['image/jpeg', 'image/png', 'image/webp', 'image/vnd.adobe.photoshop', 'application/pdf', 'application/ogg', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'text/plain', 'audio/aac', 'audio/amr', 'audio/ac3', 'audio/mp3', 'audio/mp4', 'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/wave', 'audio/webm', 'audio/x-pn-wav', 'audio/x-wav', 'video/mp4', 'video/avi', 'video/ogg', 'video/webm'];

		// if (isset($files[$file_name]) && !in_array(mime_content_type($files[$file_name]->file), $permitted_types)) {
		// 	 $is_correct = false; $http_status = 415; $msg .= 'MIME type is not supported';
		// }
		return ["is_correct" => $is_correct, 'status' => $http_status, "msg" => $msg];
	}
}
