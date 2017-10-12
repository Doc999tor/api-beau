<?php

namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SignatureCtrl extends Controller {
	public function addSignature (Request $request, Response $response) {
		$files = $request->getUploadedFiles();

		$file_name = 'sign';
		$error = '';
		if (!isset($files[$file_name])) {
			$error = 'sign fields has to exist';
		}

		$file = $files[$file_name];
		if ($file->getSize() === 0) { $error = $file_name . ' came empty' . "<br>"; }
		if ($file->getSize() > \Lib\Helpers\Utils::returnBytes('10m')) { $error = $file_name . ' too big, more than 10mb' . "<br>"; }
		if (substr($file->getClientMediaType(), 0, 6) !== 'image/') { $error = $file_name . '\'s MIME type is incorrect' . "<br>"; }

		$extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
		$filename = preg_replace('/^php/', '', basename($file->file, '.tmp'));

		$file->moveTo('image' . DIRECTORY_SEPARATOR . "{$file_name}-{$filename}.{$extension}");

		$response->getBody()->write($error);
		if ($error) {
			return $response->withStatus(400);
		}
		return $response->withStatus(204);
	}
	public function deleteSignature (Request $request, Response $response) {
		if ($request->getBody()->getSize()) {
			$response->getBody()->write('body has to be empty');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}
}