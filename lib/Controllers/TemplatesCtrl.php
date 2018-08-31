<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Slim\Container as Container;

class TemplatesCtrl extends Controller {

	private $templates_list;
	function __construct(Container $container) {
		parent::__construct($container);
		$this->templates_list = [
		  "business_name" => ["value" => "Hair Style Ashdod", "average_length" => 17],
		  "business_phone_number" => ["value" => "0541234567", "average_length" => 10],
		  "business_address" => ["value" => "Rival St 32, Tel Aviv-Yafo", "average_length" => 26],
		  "business_facebook_link" => ["value" => "https://www.facebook.com/bewebmaster", "average_length" => 36],
		  "business_website_link" => ["value" => "http://aquaplants.co.il", "average_length" => 23],
		  "client_first_name" => ["value" => "ישראל", "average_length" => 6],
		  "client_last_name" => ["value" => "ישראלי", "average_length" => 6],
		  "client_next_appointment_date" => ["value" => "2017-11-01", "average_length" => 10],
		  "client_next_appointment_time" => ["value" => "11:11", "average_length" => 5],
		  "client_next_appointment_services_list" => ["value" => "hair styling, Massage, Acupuncture", "average_length" => 34],
		  "online_booking_link" => ["value" => "http://fashion-in-israel.com", "average_length" => 28]
		];
	}

	public function getAll (Request $request, Response $response) {
		return $response->withJson(json_encode($this->templates_list, JSON_HEX_QUOT));
	}

	public function getOne (Request $request, Response $response, array $args):Response {
		$template_name = filter_var($args['template_name'], FILTER_SANITIZE_STRING);
		return $response->withJson(json_encode($this->templates_list[$template_name] ?? ''));
	}

	public function add (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_correct = true;
		$msg = '';

		$tag_list = array_keys($this->templates_list);
		preg_match_all('/(\$\$(?P<tag>\w+)\$\$)/', $body['text'], $used_tags);

		$excess_tags = array_diff($used_tags['tag'], $tag_list);
		if (count($excess_tags)) {
			$is_correct = false; $msg .= implode(',', $excess_tags) . ' tags are not this list: ' . implode(',', $tag_list);
		}
		if (!mb_strlen($body['name'])) {
			$is_correct = false; $msg .= 'name can\'t be empty';
		}
		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d H:i:s', $body['added'])) { $is_correct = false; $msg .= 'added has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>'; }


		if ($is_correct) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>\n" . $msg);
			return $response->withStatus(400);
		}
	}
}