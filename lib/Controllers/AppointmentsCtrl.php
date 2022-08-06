<?php
namespace Lib\Controllers;

use Slim\Container as Container;
use \Lib\Controllers\Controller as Controller;
use \Lib\Controllers\ServicesCtrl as ServicesCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AppointmentsCtrl extends Controller {
	private $faker;

	function __construct(Container $container) {
		parent::__construct($container);
		$this->faker = \Faker\Factory::create();
	}

	public function getCalendar (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		# category_id
		if (isset($params['category_id'])) {
			if (ctype_digit($params['category_id'])) {
				return $response->withJson($this->createAppointments());
			} else {
				$response->getBody()->write('category_id has to be an integer');
				return $response->withStatus(400);
			}
		} else if (isset($params['service_id'])) {
			$ids = json_decode('[' . $params['service_id'] . ']');
			if (empty(array_filter($ids, function ($v) {
				return !is_numeric($v);
			}))) {
				return $response->withJson($this->createAppointments());
			} else {
				$response->getBody()->write('service_id has to be an integers list');
				return $response->withStatus(400);
			}
		}

		# regular appointments api
		if (!isset($params['worker_id']) || !ctype_digit($params['worker_id'])) {
			$response->getBody()->write('worker_id has to be an integer');
			return $response->withStatus(400);
		} else {
			try {
				if (empty($params['start']) || empty($params['end'])) { throw new \Exception(); }

				$start = new \DateTime(filter_var($params['start'], FILTER_SANITIZE_STRING));
				$end = new \DateTime(filter_var($params['end'], FILTER_SANITIZE_STRING));

				$period = new \DatePeriod($start, new \DateInterval('P1D'), $end);

				$appointments = [];
				$days_count = 0;
				foreach ($period as $date) {
					if (!rand(0,3)) { continue; } # randomly no events
					if (++$days_count > 100) { break; }

					$hours_range = range(10, 19);
					shuffle($hours_range);
					$hours = array_slice($hours_range, 0, rand(0, count($hours_range)));
					sort($hours);

					for ($i=0, $count_hours = count($hours); $i < $count_hours; $i++) {
						$datetime = (clone $date)->add(new \DateInterval("PT{$hours[$i]}H" . (rand(0,3)*15) . 'M'));

						$appointments []= $this->generateAppointment($datetime);
					}
				}

				if (count($appointments)) {
					$middle_element = round(count($appointments) / 2);
					$appointments[$middle_element]['client_id'] = 1;
					$appointments[$middle_element]['profile_image'] = 'banner (1600x800).jpg';
				}

				usort($appointments, function ($a, $b) {
					return new \DateTime($a['start']) < new \DateTime($b['start']);
				});

				return $response->withJson($appointments);

			} catch (\Exception $e) {
				var_dump($e->getMessage());
				$response->getBody()->write('start and end have to exist and to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54');
				return $response->withStatus(400);
			}
		}
	}
	public function getCalendarCabinet (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		$start = (new \DateTime())->sub(new \DateInterval('P12D'));
		$end = (new \DateTime())->add(new \DateInterval('P24D'));

		$period = new \DatePeriod($start, new \DateInterval('P4D'), $end);

		$appointments = [];
		$valid_fields = ['id', 'start', 'end', 'total_price', 'services', 'client_note', 'zoom_link'];
		foreach ($period as $date) {
			if (!rand(0,3)) { continue; } # randomly no events

			$hours_range = range(10, 19);
			shuffle($hours_range);
			$hours = array_slice($hours_range, 0, rand(0, count($hours_range)));
			sort($hours);

			for ($i=0, $count_hours = count($hours); $i < $count_hours; $i++) {
				$datetime = (clone $date)->add(new \DateInterval("PT{$hours[$i]}H" . (rand(0,3)*15) . 'M'));

				$appointment = $this->generateAppointment($datetime);
				foreach ($appointment as $key => $value) {
					if (!in_array($key, $valid_fields)) {
						unset($appointment[$key]);
					}
				}
				$appointments []= $appointment;
			}
		}

		if (count($appointments)) {
			$middle_element = round(count($appointments) / 2);
			$appointments[$middle_element]['client_id'] = 1;
			$appointments[$middle_element]['profile_image'] = 'banner (1600x800).jpg';
		}

		usort($appointments, function ($a, $b) {
			return new \DateTime($a['start']) < new \DateTime($b['start']);
		});

		return $response->withJson($appointments);
	}
	public function getAvailableSlots (Request $request, Response $response):Response {
		$params = $request->getQueryParams();

		$is_params_correct = $this->checkAvailableSlotsParams($params);
		if (!$is_params_correct['is_correct']) {
			$body = $response->getBody();
			$body->write($is_params_correct['msg']);
			return $response->withStatus(400);
		}

		$start_time = (new \DateTime())->setTime(10, 0);
		$max_avaiable_slots = 8 * 4 + 1; # 10:00-18:00 including
		$slots_order_nums = rand(0,3) ? array_rand(range(1, $max_avaiable_slots), rand(8, 15)) : [];
		sort($slots_order_nums);
		$slots_start_times = array_map(function (int $offset) use ($start_time) {
			return ['time' => (clone $start_time)->add(new \DateInterval('PT' . $offset * 15 . 'M'))->format('H:i')];
		}, $slots_order_nums);
		return $response->withJson($slots_start_times);
	}

	public function singleChange (Request $request, Response $response, array $args):Response {
		$appointment_id = filter_var($args['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
		$body = $request->getParsedBody();
		// $body = json_decode($request->getBody()->getContents(), true);

		// var_dump(isset($body['end']) && !\DateTime::createFromFormat('Y-m-d H:i:s', $body['end']));

		$response_body = $response->getBody();
		if (isset($body['start'])) { # reschedule
			if (!\DateTime::createFromFormat('Y-m-d H:i:s', $body['start'])) {
				$response_body->write('start has to exist and to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54');
				return $response->withStatus(400);
			} else {
				return $response->withJson($this->createCalendarResponseObj());
			}
		} else if (isset($body['end'])) { # change duration
			if (!\DateTime::createFromFormat('Y-m-d H:i:s', $body['end'])) {
				$response_body->write('end has to exist and to be YYYY-MM-DD hh:mm:ss format, like 2017-12-18 02:09:54');
				return $response->withStatus(400);
			} else {
				return $response->withStatus(204);
			}
		} else {
			$response_body->write('body cannot be empty');
			return $response->withStatus(400);
		}
	}

	public function undelete (Request $request, Response $response, array $args):Response {
		$appointment_id = filter_var($args['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
		$body = json_decode($request->getBody()->getContents(), true);

		if (!is_bool($body['is_deleted'])) {
			$response_body = $response->getBody();
			$response_body->write('is_deleted has to be true or false');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}

	public function pay (Request $request, Response $response, array $args):Response {
		$appointment_id = filter_var($args['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
		$body = json_decode($request->getBody()->getContents(), true);

		if (!is_numeric($body['prepayment'])) {
			$response_body = $response->getBody();
			$response_body->write('prepayment has to be a number');
			return $response->withStatus(400);
		} else {
			return $response->withStatus(204);
		}
	}

	public function delete (Request $request, Response $response, array $args):Response {
		// $appointment_id = filter_var($args['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
		return $response->withJson($this->createCalendarResponseObj());
	}

	public function getCalendarSettings(Request $request, Response $response) {
		$week_types = ['daily', 'weekly', 'monthly', 'agenda'];
		$slot_duration_options = [5,10,15,20,30,60];
		return $response->withJson([
			'calendarAllowSchedulingOutsideOfTimeSlots' => (bool) rand(0,1),
			'calendarDefaultView' => $week_types[array_rand($week_types)],
			'slotDuration' => '00:' . str_pad($slot_duration_options[array_rand($slot_duration_options)], 2, '0', STR_PAD_LEFT) . ':00',
			'minTime' => str_pad(8 + rand(-1, 1), 2, '0', STR_PAD_LEFT) . ':00',
			'maxTime' => (22 + rand(-1, 1)) . ':00',
			'calendarViewStartsOn' => rand(0,1),
			'eventOverlap' => (bool) rand(0,1),
		]);
	}
	public function getHolidays (Request $request, Response $response) {
		$params = $request->getQueryParams();
		$year = (\DateTime::createFromFormat('Y', @$params['year']))->format('Y');
		if (!$year || substr($year, 0, 2) !== '20' || strlen($year) !== 4) {
			$body = $response->getBody();
			$body->write('year should be a valid YYYY date');
			return $response->withStatus(400);
		}

		$holidays = include './holidays.php';
		return $response->withJson($holidays);
	}

	private function generateAppointment(\DateTime $start) {
		$services_count = rand(1, 5);
		$duration = rand(1, 8) * 30;
		$phone = rand(1000000, 999999999);

		$total_price = rand(0,50) * 10;

		$prepayment_chances = rand(1,3);
		if ($prepayment_chances === 1) {
			$prepayment = $total_price;
		} else if ($prepayment_chances === 2) {
			$prepayment = rand(0, $total_price);
		} else { $prepayment = 0; }

		$appointment = [
			"id" => (string) rand(1, 1000),
			"start" => $start->format('Y-m-d H:i'),
			'end' => (clone $start)->add(new \DateInterval('PT' . ( (int) ($duration/60) ) .'H' . ($duration%60) . 'M'))->format('Y-m-d H:i'),
			'total_price' => (string) $total_price,
			'prepayment' => (string) $prepayment,
			'phone' => '0' . $phone,
			"services" => array_map(function ($v) {
				return ServicesCtrl::generateServiceCalendar(rand(1, 50));
			}, array_fill(0, $services_count, null)),
			'is_reminders_set' => (bool)rand(0,1),
			'is_booked_remotely' => (bool)rand(0,1),
			'is_recurring' => !(bool)rand(0,3),
			'off_time' => null,
		];
		$appointment['price_before_discount'] = (string) (rand(0,3) ? round($appointment['total_price'] / (rand(70, 99) / 100 )) : $appointment['total_price']);
		// $duration_obj = (new \DateTime($appointment['start']))->diff(new \DateTime($appointment['end']));
		// $appointment['duration'] = $duration_obj->days * 24 * 60 + $duration_obj->h * 60 + $duration_obj->i;

		if (rand(0,3)) { # group appointment
			$clients = [];
			$clients_count = rand(1,5);
			for ($i=0; $i < $clients_count; ++$i) {
				$client_id = rand(1, 120);
				$phone = rand(1000000, 999999999);
				$client = [
					'phone' => '0' . $phone,
					'client_id' => (string) $client_id,
					'name' => $this->faker->name,
					'profile_image' => $client_id . '.jpg',
					'birthdate' => ((new \DateTime())->sub(new \DateInterval('P' . (6000 + rand(0,14000)) . 'D')))->format('m-d'), // new date between 15-50 years ago;
					'status' => $this->faker->sentence(rand(1,15)),
					'is_unsubscribed' => (bool) rand(0,1),
				];
				if (rand(0,2)) {
					$client['telegram'] = 'doc999tor';
				}
				$clients []= $client;
			}
			$appointment['clients'] = $clients;
		}

		if (rand(0,5)) {
			$client_id = rand(1, 120);
			$appointment['client_id'] = (string) $client_id;
			$appointment['name'] = $this->faker->name;
			$appointment['profile_image'] = $client_id . '.jpg';
			$appointment['permit_ads'] = (bool) rand(0,3);
			$appointment['is_unsubscribed'] = !rand(0,4);
			$appointment['birthdate'] = ((new \DateTime())->sub(new \DateInterval('P' . (6000 + rand(0,14000)) . 'D')))->format('m-d'); // new date between 15-50 years ago;
		}
		if (rand(0,1)) {
			$appointment['address'] = $this->faker->address;
		}
		if (rand(0,1)) {
			$appointment['note'] = implode("\n", $this->faker->paragraphs(rand(1,3)));
		}
		if (rand(0,1)) {
			$appointment['client_note'] = trim("Pls don't be late\n" . implode("\n", $this->faker->paragraphs(rand(0,2))));
		}
		if (rand(0,1)) {
			$appointment['zoom_link'] = "https://us02web.zoom.us/j/repito.app\nID: 123123123\nPasscode: 456789";
		}
		if (!rand(0,10)) {
			$appointment['is_new_client'] = true;
		}
		if (!rand(0,6)) {
			$appointment['durationEditable'] = true;
		}
		if (!rand(0,7)) {
			$appointment['has_debt'] = true;
		}
		if (!rand(0,3)) {
			$appointment['status'] = $this->faker->sentence(rand(1,15));
		}

		if (!rand(0,4)) {
			$appointment['off_time'] = rand(0,1) ? 'break' : 'meeting';
			unset($appointment['client_id']);
			unset($appointment['name']);
			unset($appointment['phone']);
			unset($appointment['birthdate']);
			unset($appointment['services']);
			unset($appointment['total_price']);
			unset($appointment['price_before_discount']);
			unset($appointment['profile_image']);
			unset($appointment['is_new_client']);
			unset($appointment['durationEditable']);
			unset($appointment['has_debt']);
		}

		return $appointment;
	}

	public function addAppointment (Request $request, Response $response):Response {
		$body = json_decode($request->getBody()->getContents(), true);
		// $body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkAppointmentCorrectness($body);
		if ($is_body_correct['is_correct']) {
			$status = rand(0,5) ? 201 : 422;
			if ($status === 201) {
				$response_obj = $this->createCalendarResponseObj();

				$start = new \DateTime($body['start']);
				$added_appointment = $this->generateAppointment(clone $start);
				$added_appointment['start'] = $start->format('Y-m-d H:i');
				$duration = $body['duration'];
				$added_appointment['end'] = (clone $start)->add(new \DateInterval('PT' . ( (int) ($duration/60) ) .'H' . ($duration%60) . 'M'))->format('Y-m-d H:i');
				$added_appointment['off_time'] = null;
				$added_appointment['total_price'] = $body['total_price'];
				$added_appointment['note'] = $body['note'];
				$added_appointment['address'] = $body['address'];
				$added_appointment['worker_id'] = $body['worker_id'];

				$response_obj['appointment_data'] = $added_appointment;
				return $response->withStatus($status)->withJson($response_obj);
			} else { return $response->withStatus($status); }
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function editAppointment (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		if (!isset($body['off_time'])) {
			$is_body_correct = $this->checkAppointmentCorrectness($body);
			if ($is_body_correct['is_correct']) {
				$response_obj = $this->createCalendarResponseObj();

				$start = new \DateTime($body['start']);
				$edited_appointment = $this->generateAppointment(clone $start);
				$edited_appointment['start'] = $start->format('Y-m-d H:i');
				$duration = $body['duration'];
				$edited_appointment['end'] = (clone $start)->add(new \DateInterval('PT' . ( (int) ($duration/60) ) .'H' . ($duration%60) . 'M'))->format('Y-m-d H:i');
				$edited_appointment['off_time'] = null;
				$edited_appointment['total_price'] = $body['total_price'];
				$edited_appointment['note'] = $body['note'];
				$edited_appointment['address'] = $body['address'];
				$edited_appointment['worker_id'] = $body['worker_id'];

				$edited_appointment['is_recurring'] = $body['recurring_total_amount'] !== 0;

				$response_obj['appointment_data'] = $edited_appointment;
				return $response->withJson($response_obj);
			} else {
				$body = $response->getBody();
				$body->write($is_body_correct['msg']);
				return $response->withStatus(400);
			}
		} else {
			$is_body_correct = $this->checkMeetingCorrectness($body);
			if ($is_body_correct['is_correct']) {
				return $response->withStatus(204);
			} else {
				$body = $response->getBody();
				$body->write($is_body_correct['msg']);
				return $response->withStatus(400);
			}
		}
	}
	public function addMeeting (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkMeetingCorrectness($body);
		if ($is_body_correct['is_correct']) {
			if ($body['recurring_total_amount'] == 99) {
				$dates_count = rand(1, 6);
				$dates = [];
				for ($i=0; $i < $dates_count; $i++) {
					$interval = rand(1, 15);
					$dates []= (new \DateTime($body['start']))->add(new \DateInterval("P{$interval}D"))->format('Y-m-d H:i:s');
				}
				sort($dates);
				return $response->withStatus(409)->withJson([ "error" => "םverlapping",  "overlappingEvents" => $dates ]);
			} else {
				return $response->withStatus(201)->withJson([ "appointment_id" => rand(0, 150), "is_notification_sent" => false, ]);
			}
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function addBreak (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBreakCorrectness($body);
		if ($is_body_correct['is_correct']) {
			if ($body['recurring_total_amount'] == 99) {
				$dates_count = rand(1, 6);
				$dates = [];
				for ($i=0; $i < $dates_count; $i++) {
					$interval = rand(1, 15);
					$dates []= (new \DateTime($body['start']))->add(new \DateInterval("P{$interval}D"))->format('Y-m-d H:i:s');
				}
				sort($dates);
				return $response->withStatus(409)->withJson([ "error" => "םverlapping",  "overlappingEvents" => $dates ]);
			} else {
				return $response->withStatus(201)->withJson([ "appointment_id" => rand(0, 150), "is_notification_sent" => false, ]);
			}
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	public function addVacation (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBreakCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201)->withJson([ "appointment_id" => rand(0, 150), "is_notification_sent" => false, ]);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkAppointmentCorrectness (array $body): array {
		$correct_body = ['client_id', 'clients', 'phone', 'services', 'start', 'duration', 'is_reminders_set', 'note', 'client_note', 'zoom_link', 'total_price', 'prepayment', 'recurring_step_days', 'recurring_total_amount', 'address', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if (!isset($body['start']) || !\date_create($body['start'])) { $is_correct = false; $msg .= ' start has to be YYYY-MM-DDThh:mm:ss format, like 2017-12 18T02:09:54 <br>'; }

		if (isset($body['client_id']) && !preg_match('/^-?\d+$/', $body['client_id'])) { $is_correct = false; $msg .= 'client_id has to be a positive integer or -1 for occasional client <br>'; }
		if (isset($body['clients'])) {
			$clients = $body['clients'];
			if (count(array_filter($clients, 'is_int')) !== count($clients)) {
				$is_correct = false; $msg .= 'clients has to be an array of integers, -1 is not permitted <br>';
			}
		}

		$services = is_array($body['services']) ? $body['services'] : json_decode($body['services']);
		if (gettype($services) !== 'array' || count(array_filter($services, function ($s) {
			return is_int($s['id']) && (empty($s['count']) || (!empty($s['count']) && is_int($s['count'])));
		})) !== count($services)) { $is_correct = false; $msg .= ' services have to be an array of {id: int, count: int} <br>'; }
		if (!isset($body['duration']) || !is_numeric($body['duration'])) { $is_correct = false; $msg .= ' duration has to be an integer <br>'; }

		if (!isset($body['is_reminders_set']) || (!in_array($body['is_reminders_set'], ['true', 'false']) && !is_bool($body['is_reminders_set']))) { $is_correct = false; $msg .= ' is_reminders_set has be be true or false <br>'; }

		if (!isset($body['total_price']) || !is_numeric($body['total_price'])) { $is_correct = false; $msg .= ' total_price has to be a number <br>'; }
		if (!isset($body['prepayment']) || !is_numeric($body['prepayment'])) { $is_correct = false; $msg .= ' prepayment has to be a number <br>'; }

		if (isset($body['recurring_step_days']) && !ctype_digit($body['recurring_step_days'])) { $is_correct = false; $msg .= ' recurring_step_days has to be an integer <br>'; }
		if (isset($body['recurring_total_amount']) && !ctype_digit($body['recurring_total_amount'])) { $is_correct = false; $msg .= ' recurring_total_amount has to be an integer <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) { $is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\date_create($body['added'])) { $is_correct = false; $msg .= ' added has to be YYYY-MM-DDThh:mm:ss format, like 2017-12-18T02:09:54 <br>'; }

		return ['is_correct' => $is_correct, "msg" => $msg];
	}
	private function checkMeetingCorrectness (array $body): array {
		$correct_body = ['off_time', 'start', 'duration', 'end', 'is_all_day', 'note', 'recurring_step_days', 'recurring_total_amount', 'address', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if ((!isset($body['start']) || !\date_create($body['start']))) { $is_correct = false; $msg .= 'start has to exist and to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18 02:09:54 <br>'; }

		if (isset($body['is_all_day']) && !in_array($body['is_all_day'], ['true', 'false'])) { $is_correct = false; $msg .= ' is_all_day can be true or false only <br>'; }
		if (isset($body['recurring_step_days']) && !ctype_digit($body['recurring_step_days'])) { $is_correct = false; $msg .= ' recurring_step_days has to be an integer <br>'; }
		if (isset($body['recurring_total_amount']) && !ctype_digit($body['recurring_total_amount'])) { $is_correct = false; $msg .= ' recurring_total_amount has to be an integer <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) { $is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\date_create($body['added'])) { $is_correct = false; $msg .= ' added has to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18T02:09:54 <br>'; }

		return ['is_correct' => $is_correct, "msg" => $msg];
	}
	private function checkBreakCorrectness (array $body): array {
		$correct_body = ['start', 'duration', 'recurring_step_days', 'recurring_total_amount', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		if ((!isset($body['start']) || !\date_create($body['start']))) { $is_correct = false; $msg .= 'start has to exist and to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18 02:09:54 <br>'; }

		if (isset($body['recurring_step_days']) && !ctype_digit($body['recurring_step_days'])) { $is_correct = false; $msg .= ' recurring_step_days has to be an integer <br>'; }
		if (isset($body['recurring_total_amount']) && !ctype_digit($body['recurring_total_amount'])) { $is_correct = false; $msg .= ' recurring_total_amount has to be an integer <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) { $is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\date_create($body['added'])) { $is_correct = false; $msg .= ' added has to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18T02:09:54 <br>'; }

		return ['is_correct' => $is_correct, "msg" => $msg];
	}

	private function checkAvailableSlotsParams(array $params) {
		$correct_body = ['date', 'worker_id', 'duration'];

		$is_correct = true; $msg = '';

		if ((!isset($params['date']) || !\DateTime::createFromFormat('Y-m-d', $params['date']))) { $is_correct = false; $msg .= 'date has to exist and to be YYYY-MM-DD format, like 2020-01-01 <br>'; }

		if (!isset($params['worker_id']) || !ctype_digit($params['worker_id'])) { $is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($params['duration']) || !ctype_digit($params['duration'])) { $is_correct = false; $msg .= ' duration has to be an integer <br>'; }

		return ['is_correct' => $is_correct, "msg" => $msg];
	}

	public function getRecentAppointments (Request $request, Response $response) {
		$appointments = [];
		$today = new \DateTime();
		$count = rand(6, 10);
		# future appointments
		for ($i=0, $c = floor($count/3); $i < $c; $i++) {
			$diff_days = rand(1,20);
			$interval = new \DateInterval("P{$diff_days}D");
			$date = (clone $today)->add($interval)->setTime(rand(10,16), rand(0,1)?0:30, 0);
			$appointments []= $this->generateAppointment($date);
		}

		#past appointments
		for ($i=0, $c = ceil($count/3*2); $i < $c; $i++) {
			$diff_days = rand(1,60);
			$interval = new \DateInterval("P{$diff_days}D");
			$date = (clone $today)->sub($interval)->setTime(rand(10,16), rand(0,1)?0:30, 0);
			$appointments []= $this->generateAppointment($date);
		}

		for ($i=0, $c = count($appointments); $i < $c; $i++) {
			if (!empty($appointments[$i]['off_time'])) {
				unset($appointments[$i]);
			}
		}

		return $response->withJson(array_values($appointments));
	}

	private function createAppointments () {
		$appointments = [];
		if (rand(1,3) % 3 !== 0) {
			$range_dates = range(1, rand(5,50), rand(1,5));
			shuffle($range_dates);
			$range_dates = array_slice($range_dates, 0, rand(1,20));
			sort($range_dates);

			$today = new \DateTime();
			$appointments = [];
			for ($i=0, $appointments_limit = count($range_dates); $i < $appointments_limit; $i++) {
				$interval = new \DateInterval("P{$range_dates[$i]}D");
				$date = (clone $today)->sub($interval);
				$appointments []= $this->generateAppointment($date);
			}
		}
		return $appointments;
	}

	private function createCalendarResponseObj() {
		$response = [
			"is_notification_sent" => true, // показываем или нет вторую строку в зеленом тосте
			"is_sms_failed" => false, // показываем или нет красный тост
		];

		if (!rand(0,2)) { // false
			$response['is_notification_sent'] = false;
			if (rand(0,2)) {
				$response['is_sms_failed'] = true;
				$possible_error_reason = ['no_sms', 'no_phone', 'phone_not_valid', 'sending_failure'];
				$response['error_reason'] = $possible_error_reason[rand(0, count($possible_error_reason)-1)]; // no_sms | no_phone | phone_not_valid | sending_failure
			}
		} else {
			if (!rand(0,2)) {
				$response['is_warning'] = true; // показываем или нет желтый тост
				$response['warning_reason'] = 'no_sms'; // no_sms - пока только один вариант
			}
		}
		return $response;
	}
}
