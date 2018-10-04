<?php
namespace Lib\Controllers\CustomersDetails;

use Lib\Controllers\Controller as Controller;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Lib\Helpers\Utils;

class ColorsCtrl extends Controller {
	public function getColorsBeautechOld (Request $request, Response $response):Response {
		$colors = [];

		for ($i=0, $colors_count = rand(1,3); $i < $colors_count; $i++) {
			$colors []= $this->generateColorBeautechOld();
		}

		return $response->withJson($colors);
	}


	public function generateColorBeautechOld() {
		$color = [ "id" => rand(1, 51) ];

		$service = \Lib\Controllers\ServicesCtrl::generateService(rand(1, 45));
		$color['type'] = 'Wella SP';
		$color['series'] = Utils::generateWord();

		$possible_service_counts = [3, 5, 7, 10, 20];
		$color['service_count'] = $possible_service_counts[array_rand($possible_service_counts)];
		$color['sum'] = ($color['service_count'] - 1*rand(0,1)) * $service['price'];

		$color['date'] = (new \DateTime())
			->modify(rand(0,180) . ' days ago')
			->format('Y-m-d H:i');

		if (!(rand() % 3)) {
			$color['comments'] = Utils::generatePhrase();
		}

		if (!(rand() % 3)) {
			$color['oxy'] = [];
			$uses_count = rand(1, 3);

			for ($i=1; $i <= $uses_count; $i++) {
				$color['oxy'] []= [
					'percent' => (string)rand(1, 9),
					'dosing' => (string)(rand(1, 6) * 10)
				];
			}
		}

		return $color;
	}
}