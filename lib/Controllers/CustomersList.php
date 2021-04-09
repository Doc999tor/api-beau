<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Lib\Helpers\Utils;

class CustomersList extends Controller {
	public function index (Request $request, Response $response):Response {
		$path = 'customers-list';
		$static_prefix = str_repeat('../', substr_count($request->getUri()->getPath(), '/'));
		$base_path = $request->getUri()->getBasePath();

		return $this->view->render($response, $path . '.html', [
			'base_path' => $base_path,
			'prefix' => $static_prefix,
			'path' => $path,
		]);
	}

	public function getClients (Request $request, Response $response) {
		$params = $request->getQueryParams();
		if (!empty($params['phone'])) {
			$number = $params['phone'];
			if (!preg_match('/^[\d()+\-*\/]+$/', $number)) { return $response->withStatus(400)->getBody()->write('phone has to be a correct phone number, got ' . $number); }
			$clients = [];
			for ($i=0, $limit = rand(0, 3); $i < $limit; $i++) {
				$clients []= $this->generateClient();
				$clients[$i]['phone'] = $number;
			}
			return $response->withJson($clients);
		} else {
			if (!isset($params['limit'])) {
				return $response->withStatus(400);
			}
			if (!isset($params['offset'])) {
				$params['offset'] = 0;
			}
			$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';
		}

		return $response->withJson(self::generateClients($params['limit'], $q));
	}

	public function deleteClients (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		// if (!\DateTime::createFromFormat('Y-m-d H:i:s', $body['date'])) { $is_correct = false; $msg .= "date has to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54<br>"; }

		if (empty($body['clients']) || preg_match('/^\d[,\d]*$/', $body['clients'])) {
			return $response->withStatus(204);
		} else {
			$response_body = $response->getBody();
			$response_body->write("clients body param is incorrect: $body[clients] <br>");
			return $response->withStatus(400);
		}
	}
	public static function generateClients($limit, $q = '') {
		$clients = [];

		++$limit;
		for ($i=1; $i < $limit; $i++) {
			$clients []= self::generateClient($q, $i);
		}

		return $clients;
	}
	public static function generateClient($q = '', $id = null) {
		$id = $id ?? rand(0, 300);
		$phone = rand(1000000, 999999999);

		$source = '';
		if (rand(0,3)) {
			$source_variants = self::getSourceVariants();
			$source = $source_variants[array_rand($source_variants)];
		}

		$tags = '';
		if (rand(0,3)) {
			$tags_variants = self::getTagsVariants();
			shuffle($tags_variants);
			$tags = implode(' #', array_slice($tags_variants, 0, rand(0, count($tags_variants) / 2)));
		}

		$client = [
			'id' => $id,
			'profile_image' => "{$id}.jpg",
			'name' => Utils::generatePhrase($q, 1, 2),
			'permit_ads' => (bool) rand(0,3),
			'is_unsubscribed' => !rand(0,4),
			'status' => Utils::generatePhrase('', 1, 4),
			'source' => $source,
			'tags' => $tags ? '#' . $tags : $tags,
			'registration_date' => (new \DateTime())->sub(new \DateInterval('P' . (361 + rand(0,1805)) . 'D'))->format('Y-m-d'), // new date between 1-5 years ago;
		];
		if (rand(0,3)) {
			$client['phone'] = '0' . $phone;
			$client['phone_canonical'] = '+38' . $phone;
			$client['email'] = Utils::generateWord() . '@email.com';
			$client['gender'] = rand(0,3) ? 'male' : 'female';
		}
		if (rand(0,2)) {
			$client['address'] = Utils::getRandomAddress();
			$birthdate = (new \DateTime())->sub(new \DateInterval('P' . (6000 + rand(0,14000)) . 'D')); // new date between 15-50 years ago;
			$client['birthdate'] = $birthdate->format('m-d');
			$client['birthyear'] = $birthdate->format('Y');
		}
		if (rand(0,5)) {
			$client['last_appointment'] = date("Y-m-d", rand(time() - 3600 * 24 * 90, time() + 3600 * 24 * 30)) . ' ' . str_pad(rand(9,20), 2, '0', STR_PAD_LEFT) . ':' . (rand(0,1) ? '30' : '00'); # 3 months back and 1 forth
		}
		if (rand(0,10) < 9) {
			$client['next_appointment'] = date("Y-m-d H:i", rand(time(), time() + 3600 * 24 * 30)); # 1 month forth,
		}

		return $client;
	}

	public function validatePhoneNumber (Request $request, Response $response, array $args):Response {
		$body = $request->getParsedBody();
		$phone = filter_var($body['phone'], FILTER_SANITIZE_STRING);

		if (!preg_match('/^[\d\s()+*#-]+$/', $phone)) {
			return $response->withStatus(422);
		} else {
			return $response;
		}
	}
	public function checkPhoneNumberExists (Request $request, Response $response, array $args):Response {
		$number = filter_var($args['number'], FILTER_SANITIZE_STRING);

		$body = $response->getBody();

		if (!preg_match('/^[\d\s()+*#-]+$/', $number)) {
			$body->write("the number - $number is incorrect");
			return $response->withStatus(400);
		}

		$body->write(time() % 9 ? 'true' : 'false');
		return $response;
	}

	public function getCount (Request $request, Response $response):Response {
		return $response->withHeader('X-Total-Count', rand(50, 150));
	}

	public function importBulkClients(Request $request, Response $response) {
		$body = $request->getParsedBody();

		if (is_array($body)) {
			return $response->withStatus(202);
		} else {
			$body = $response->getBody();
			$body->write('body is incorrect: ' . json_encode($body));
			return $response->withStatus(400);
		}
	}
	public function skipImportBulkClients(Request $request, Response $response) { return $response->withStatus(204); }

	private static function getSourceVariants (): array {
		return ['facebook', 'instagram', 'рекомендация_клиента', 'сайт', 'местная_газета', 'подобрал_на_помойке'];
	}
	private static function getTagsVariants (): array {
		return ['лид', 'клиент', 'платит', 'новый', 'старый', 'vip', 'регулярно_ходит', 'давно_не_был', 'не_заплатил', 'опаздывает', ];
	}

}
