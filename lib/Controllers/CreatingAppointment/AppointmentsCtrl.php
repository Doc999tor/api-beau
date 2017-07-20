<?php

namespace Lib\Controllers\CreatingAppointment;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AppointmentsCtrl extends Controller {
	public function saveData (Request $request, Response $response) {
		// $params = $request->getQueryParams();
		$params = $request->getParsedBody();

		$must_fields = ['date', 'start', 'end', 'client_id', 'procedure'];
		$canbe_fields = ['comments'];

		for ($i=0, $count = count($must_fields); $i < $count; $i++) {
			if (!isset($params[$must_fields[$i]])) {
				$response = $this->setResponseBody($response, 'Field ' . $must_fields[$i] . ' has to be an array of integers');
			}
			switch ($must_fields[$i]) {
				case 'procedure':
					if (is_null(json_decode($params[$must_fields[$i]]))) {
						$response = $this->setResponseBody($response, 'Field ' . $must_fields[$i] . ' has to be an array of integers');
					}
					break;

				default: break;
			}
		}

		if (count($must_fields) !== count($params)) {
			$response = $this->setResponseBody($response, 'Number of fields is incorrect');
		}

		array_walk($canbe_fields, function ($field) {
			if (isset($params[$field]) && empty($params[$field])) {
				$this->setResponseBody($response, "Field {$canbe_fields[$i]} if passed, should not be empty");
			}
		});

		if ($response->getStatusCode() == 200) {
			$response = $response->withStatus(201);
		}
		return $response;
	}

	private function setResponseBody(Response $response, $msg, $status_code = 400) {
		$response = $response->withStatus($status_code);
		$body = $response->getBody();
		$body->write($msg . " <br>");
		return $response->withBody($body);
	}
}