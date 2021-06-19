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
					$appointments[$middle_element]['profile_picture'] = 'banner (1600x800).jpg';
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

	public function reschedule (Request $request, Response $response, array $args):Response {
		$appointment_id = filter_var($args['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
		$body = $request->getParsedBody();
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
			'slotDuration' => '00:' . $slot_duration_options[array_rand($slot_duration_options)] . ':00',
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
		$appointment = [
			"id" => (string) rand(1, 1000),
			"start" => $start->format('Y-m-d H:i'),
			'end' => (clone $start)->add(new \DateInterval('PT' . ( (int) ($duration/60) ) .'H' . ($duration%60) . 'M'))->format('Y-m-d H:i'),
			'total_price' => (string) (rand(0,50)*10),
			'phone' => '0' . $phone,
			"services" => array_map(function ($v) {
				return ServicesCtrl::generateServiceCalendar(rand(1, 50));
			}, array_fill(0, $services_count, null)),
			'is_reminders_set' => (bool)rand(0,1),
			'off_time' => null,
		];
		// $duration_obj = (new \DateTime($appointment['start']))->diff(new \DateTime($appointment['end']));
		// $appointment['duration'] = $duration_obj->days * 24 * 60 + $duration_obj->h * 60 + $duration_obj->i;
		if (rand(0,3)) { # group appointment
			$clients = [];
			$clients_count = rand(1,5);
			for ($i=0; $i < $clients_count; ++$i) {
				$client_id = rand(1, 120);
				$phone = rand(1000000, 999999999);
				$clients []= [
					'phone' => '0' . $phone,
					'client_id' => (string) $client_id,
					'name' => $this->faker->name,
					'profile_picture' => $client_id . '.jpg',
					'birthdate' => ((new \DateTime())->sub(new \DateInterval('P' . (6000 + rand(0,14000)) . 'D')))->format('m-d'), // new date between 15-50 years ago;
					'status' => $this->faker->sentence(rand(1,15)),
				];
			}
			$appointment['clients'] = $clients;
		}

		if (rand(0,5)) {
			$client_id = rand(1, 120);
			$appointment['client_id'] = (string) $client_id;
			$appointment['name'] = $this->faker->name;
			$appointment['profile_picture'] = $client_id . '.jpg';
			$appointment['permit_ads'] = (bool) rand(0,3);
			$appointment['is_unsubscribed'] = !rand(0,4);
			$appointment['birthdate'] = ((new \DateTime())->sub(new \DateInterval('P' . (6000 + rand(0,14000)) . 'D')))->format('m-d'); // new date between 15-50 years ago;
		}
		if (rand(0,1)) {
			$appointment['address'] = $this->faker->address;
		}
		if (rand(0,1)) {
			$appointment['note'] = implode('\n', $this->faker->paragraphs(rand(1,3)));
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
			unset($appointment['profile_picture']);
			unset($appointment['is_new_client']);
			unset($appointment['durationEditable']);
			unset($appointment['has_debt']);
		}

		return $appointment;
	}

	public function addAppointment (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkAppointmentCorrectness($body);
		if ($is_body_correct['is_correct']) {
			$status = rand(0,5) ? 201 : 422;
			if ($status === 201) {
				$response_obj = $this->createCalendarResponseObj();
				$response_obj['appointment_id'] = rand(1, 10000);
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
				return $response->withJson($this->createCalendarResponseObj());
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
			return $response->withStatus(201)->withJson([ "appointment_id" => rand(0, 150), "is_notification_sent" => false, ]);
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
			return $response->withStatus(201)->withJson([ "appointment_id" => rand(0, 150), "is_notification_sent" => false, ]);
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
		$correct_body = ['client_id', 'clients', 'phone', 'services', 'start', 'duration', 'is_reminders_set', 'note', 'total_price', 'address', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if (!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s', $body['start'])) { $is_correct = false; $msg .= ' start has to be YYYY-MM-DDThh:mm:ss format, like 2017-12-18T02:09:54 <br>'; }

		if (isset($body['client_id']) && !preg_match('/^-?\d+$/', $body['client_id'])) { $is_correct = false; $msg .= 'client_id has to be a positive integer or -1 for occasional client <br>'; }
		if (isset($body['clients'])) {
			$clients = json_decode($body['clients']);
			if (count(array_filter($clients, 'is_int')) !== count($clients)) {
				$is_correct = false; $msg .= 'clients has to be an array of integers, -1 is not permitted <br>';
			}
		}

		$services = json_decode($body['services']);
		if (gettype($services) !== 'array' || count(array_filter($services, function ($s) {
			return is_int($s->id) && (empty($s->count) || (!empty($s->count) && is_int($s->count)));
		})) !== count($services)) { $is_correct = false; $msg .= ' services have to be an array of {id: int, count: int} <br>'; }
		if (!isset($body['duration']) || !ctype_digit($body['duration'])) {$is_correct = false; $msg .= ' duration has to be an integer <br>'; }

		if (!isset($body['is_reminders_set']) || !in_array($body['is_reminders_set'], ['true', 'false'])) {$is_correct = false; $msg .= ' is_reminders_set has be be true or false <br>'; }

		if (!isset($body['total_price']) || !is_numeric($body['total_price'])) {$is_correct = false; $msg .= ' total_price has to be a number <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s', $body['added'])) { $is_correct = false; $msg .= ' added has to be YYYY-MM-DDThh:mm:ss format, like 2017-12-18T02:09:54 <br>'; }

		return ['is_correct' => $is_correct, "msg" => $msg];
	}
	private function checkMeetingCorrectness (array $body): array {
		$correct_body = ['off_time', 'start', 'duration', 'end', 'is_all_day', 'note', 'address', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', $diff_keys) . ' arguments should not exist' . "<br>";
		}

		if (!in_array($body['off_time'], ['appointment', 'meeting', 'break', 'vacation'])) { $is_correct = false; $msg .= ' off_time can be appointment, meeting, break or vacation only <br>'; }

		if ((!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s', $body['start']))/* || (!isset($body['end']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s', $body['end']))*/) { $is_correct = false; $msg .= 'start and end have to exist and to be YYYY-MM-DDThh:mm:ss format, like 2019-12-18T02:09:54 <br>'; }

		if (isset($body['is_all_day']) && !in_array($body['is_all_day'], ['true', 'false'])) {$is_correct = false; $msg .= ' is_all_day can be true or false only <br>'; }
		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s', $body['added'])) { $is_correct = false; $msg .= ' added has to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18T02:09:54 <br>'; }

		return ['is_correct' => $is_correct, "msg" => $msg];
	}
	private function checkBreakCorrectness (array $body): array {
		$correct_body = ['start', 'end', 'worker_id', 'added'];

		$is_correct = true; $msg = '';

		if ((!isset($body['start']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s', $body['start'])) || (!isset($body['end']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s', $body['end']))) { $is_correct = false; $msg .= 'start and end have to exist and to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18T02:09:54 <br>'; }

		if (!isset($body['worker_id']) || !ctype_digit($body['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($body['added']) || !\DateTime::createFromFormat('Y-m-d\TH:i:s', $body['added'])) { $is_correct = false; $msg .= ' added has to be YYYY-MM-DD hh:mm:ss format, like 2019-12-18T02:09:54 <br>'; }

		return ['is_correct' => $is_correct, "msg" => $msg];
	}

	private function checkAvailableSlotsParams(array $params) {
		$correct_body = ['date', 'worker_id', 'duration'];

		$is_correct = true; $msg = '';

		if ((!isset($params['date']) || !\DateTime::createFromFormat('Y-m-d', $params['date']))) { $is_correct = false; $msg .= 'date has to exist and to be YYYY-MM-DD format, like 2020-01-01 <br>'; }

		if (!isset($params['worker_id']) || !ctype_digit($params['worker_id'])) {$is_correct = false; $msg .= ' worker_id has to be an integer <br>'; }

		if (!isset($params['duration']) || !ctype_digit($params['duration'])) {$is_correct = false; $msg .= ' duration has to be an integer <br>'; }

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
