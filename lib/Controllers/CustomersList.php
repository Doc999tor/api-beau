<?php
namespace Lib\Controllers;

use Slim\Container as Container;
use \Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CustomersList extends Controller {
	private static $faker;
	function __construct(Container $container) {
		parent::__construct($container);
		self::$faker = \Faker\Factory::create();
	}

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
			if (empty($params['limit'])) {
				$params['limit'] = 1000;
			}
			if (!isset($params['offset'])) {
				$params['offset'] = 0;
			}
			$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';
		}

		$clients = self::generateClients($params['limit'], $q);

		if (!empty($params['sorting_criteria'])) {
			$sorting_criteria = $params['sorting_criteria'];
			$multiplier = 1;
			switch ($sorting_criteria) {
				case 'total_income':
					$multiplier = round(rand(0, 100000) / 100, 2);
					break;
				case 'appointment_count':
					$multiplier = 1;
					break;
			}
			foreach ($clients as &$client) {
				$client[$sorting_criteria] = round(rand(0, 100) * $multiplier, 2);
			}
			usort($clients, function($b, $a) use ($sorting_criteria) {
				return $a[$sorting_criteria] <=> $b[$sorting_criteria];
			});
		}

		return $response->withJson($clients);
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
		if (is_null(self::$faker)) {
			self::$faker = \Faker\Factory::create();
		}

		$id = $id ?? rand(0, 300);
		$phone = rand(1000000, 999999999);

		$source = null;
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
			'name' => self::$faker->name,
			'permit_ads' => (bool) rand(0,3),
			'is_unsubscribed' => !rand(0,3),
			'note' => self::$faker->sentence(rand(1,15)),
			'source' => $source,
			'tags' => $tags ? '#' . $tags : null,
			'registration_date' => (new \DateTime())->sub(new \DateInterval('P' . (361 + rand(0,1805)) . 'D'))->format('Y-m-d'), // new date between 1-5 years ago;
			'is_open_online' => (bool) rand(0, 1),
		];
		if (rand(0,3)) {
			$client['phone'] = '0' . $phone;
			$client['phone_canonical'] = '+' . $phone;
			$client['email'] = self::$faker->email;
			$client['gender'] = rand(0,3) ? 'male' : 'female';
		}
		if (rand(0,2)) {
			$client['address'] = self::$faker->address;
			$birthdate = (new \DateTime())->sub(new \DateInterval('P' . (6000 + rand(0,14000)) . 'D')); // new date between 15-50 years ago;
			$client['birthdate'] = $birthdate->format('m-d');
			$client['birthyear'] = $birthdate->format('Y');
		}
		if (rand(0,2)) {
			$client['telegram'] = 'doc999tor';
		}
		if (rand(0,2)) {
			$client['instagram'] = 'javascript.js';
		}
		if (rand(0,2)) {
			$client['custom_fields'] = [
				[
					"name" => "Waist Circumference",
					"type" => "text",
					"value" => rand(50, 150),
					"added" => (new \DateTime())->sub(new \DateInterval('P' . (rand(0,180)) . 'D'))->format('Y-m-d H:i'),
				]
			];
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
 			return rand(0, 2)
				? $response->withStatus(202)
				: $response->withStatus(402);
		} else {
			$body = $response->getBody();
			$body->write('body is incorrect: ' . json_encode($body));
			return $response->withStatus(400);
		}
	}
	public function skipImportBulkClients(Request $request, Response $response) { return $response->withStatus(204); }

	public static function getSourceVariants (): array {
		return ['facebook', 'instagram', 'рекомендация_клиента', 'сайт', 'местная_газета', 'подобрал_на_помойке'];
	}
	public static function getTagsVariants (): array {
		return ['лид', 'клиент', 'платит', 'новый', 'старый', 'vip', 'регулярно_ходит', 'давно_не_был', 'не_заплатил', 'опаздывает', ];
	}
}
