<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

### Creating Appointment
$app->group('/creating-appointment', function () use ($app) {
	$prefix = 'CreatingAppointment\\ClientsCtrl:';
	$app->get('/clients', $prefix . 'getClients');
	$app->get('/clients/{id:\d+}', $prefix . 'getClient');

	$prefix = 'CreatingAppointment\\AppointmentsCtrl:';
	$app->get('/calendar', $prefix . 'getCalendar');

	$app->post('/appointments', $prefix . 'addAppointment')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
	$app->post('/meeting', $prefix . 'addMeeting')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
	$app->post('/break', $prefix . 'addBreak')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
	$app->post('/vacation', $prefix . 'addVacation')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
});

### Adding Clients
$app->group('/add-client', function () use ($app) {
	$prefix = 'AddClientCtrl:';
	$app->get('/', $prefix . 'index');
	$app->get('/clients/{id:\d+}', $prefix . 'getClient');
	$app->get('/clients',    $prefix . 'getClients');
	$app->delete('/clients', $prefix . 'removeUser');
	$app->post  ('/clients', $prefix . 'addClient')->add(new \Lib\Middlewares\PostReturnIDMiddleware());

	$app->get('/media', $prefix . 'getMedia');
});

### Customers List
$app->group('/customers-list', function () use ($app) {
	$prefix = 'CustomersList:';
	$app->get   ('/', $prefix . 'index');
	$app->get   ('/clients', $prefix . 'getClients');
	$app->delete('/clients', $prefix . 'deleteClients');

	$app->get('/clients/check-phone-number-exists/{number}', $prefix . 'checkPhoneNumberExists');
});

### Groups
$app->group('/groups', function () use ($app) {
	$prefix = 'GroupsCtrl:';
	$app->get('/{group_id:\d+}/clients', $prefix . 'getGroupClients');
});

### Customers Details
$app->group('/customers-details', function () use ($app) {
	$prefix = 'CustomersDetails\\';
	$cl_prefix = $prefix . 'ClientsCtrl:';

	$app->get('/', $cl_prefix . 'index');

	$app->get('/clients', $cl_prefix . 'getClients');

	$app->patch('/clients/{client_id:\d+}', $cl_prefix . 'setPersonalData');

	# Dept
	$app->group('/clients/{client_id:\d+}/dept', function () use ($app, $prefix) {
		$dept_prefix = $prefix . 'DeptCtrl';
		$app->post('', $dept_prefix . ':addDept')->add(new \Lib\Middlewares\PostReturnIDMiddleware());

		$app->group('/{dept_id:\d+}', function () use ($app, $dept_prefix) {
			$app->put   ('', $dept_prefix . ':updateDept');
			$app->delete('', $dept_prefix . ':deleteDept');
		});
	});

	# Note
	$app->group('/clients/{client_id:\d+}/notes', function () use ($app, $prefix) {
		$note_prefix = $prefix . 'NotesCtrl';
		$app->post('', $note_prefix . ':addNote')->add(new \Lib\Middlewares\PostReturnIDMiddleware());

		$app->group('/{note_id:\d+}', function () use ($app, $note_prefix) {
			$app->patch ('', $note_prefix . ':updateNote');
			$app->delete('', $note_prefix . ':deleteNote');
		});
	});

	# Map
	$app->get('/clients/{client_id:\d+}/map', $cl_prefix . 'getMap');

	# Media
	$app->group('/clients/{client_id:\d+}/media', function () use ($app, $prefix) {
		$app->post('', 'CustomersDetails\MediaCtrl:addMedia')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
		$app->patch ('/{media_id:\d+}', 'CustomersDetails\MediaCtrl:editMediaNote');
		$app->delete('/{media_id:\d+}', 'CustomersDetails\MediaCtrl:removeMedia');
	});

	# Social
	$app->group('/clients/{client_id:\d+}/social', function () use ($app, $prefix) {
		$app->post('', $prefix . 'SocialCtrl:addSocial')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
		$app->delete('/{media_id:\d+}', $prefix . 'SocialCtrl:deleteSocial');
	});

	# Signature
	$app->group('/clients/{client_id:\d+}/signature', function () use ($app, $prefix) {
		$app->post('', $prefix . 'SignatureCtrl:addSignature');
		$app->delete('', $prefix . 'SignatureCtrl:deleteSignature');
	});

	$app->post('/clients/{client_id:\d+}/filling-up', $cl_prefix . 'sendLinkFillUpPersonalData');

	# Timeline
	$app->group('/clients/{client_id:\d+}/timeline', function () use ($app, $prefix) {
		$prefix = 'CustomersDetails\TimelineCtrl:';
		$app->get('/appointments', $prefix . 'getAppoinments');
		$app->get('/gallery', $prefix . 'getGallery');
		$app->get('/depts', $prefix . 'getDepts');
		$app->get('/notes', $prefix . 'getNotes');
		$app->get('/sms', $prefix . 'getSms');
		$app->get('/punch_cards', $prefix . 'getPunchCards');
	});
	# Punch_cards
	$app->group('/clients/{client_id:\d+}/punch_cards', function () use ($app) {
		$prefix = 'CustomersDetails\PunchCardsCtrl:';
		$app->post('', $prefix . 'addPunchCard')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
		$app->delete('/{punch_card_id:\d+}', $prefix . 'deletePunchCard');

		$app->post('/{punch_card_id:\d+}/use', $prefix . 'use')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
		$app->delete('/{punch_card_id:\d+}/use/{use_id:\d+}', $prefix . 'unuse');
	});
});

### Reminders
$app->group('/reminders', function () use ($app) {
	$prefix = 'RemindersCtrl:';
	$app->get   ('', $prefix . 'index');
	$app->get   ('/clients', $prefix . 'getClients');
	$app->post  ('', $prefix . 'add')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
	$app->put   ('/{reminder_id:\d+}', $prefix . 'update');
	$app->patch ('/{reminder_id:\d+}', $prefix . 'isDone');
	$app->delete('/{reminder_id:\d+}', $prefix . 'delete');
});

### Procedures
$app->group('/procedures', function () use ($app) {
	$prefix = 'ProceduresCtrl:';

	$app->get ('',    $prefix . 'getAll');
	$app->get ('/bi', $prefix . 'getBI');
	$app->get ('/{procedure_id:\d+}', $prefix . 'getOne');
	$app->post('',    $prefix . 'add')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
});

### SMS Templates
$app->group('/templates', function () use ($app) {
	$prefix = 'TemplatesCtrl:';

	$app->get ('',    $prefix . 'getAll');
	$app->get ('/{template_name:\w+}',    $prefix . 'getOne');
	$app->post('',    $prefix . 'add')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
});

$app->any('/503', function (Request $request, Response $response):Response {
	return $response->withHeader('Retry-After', 120)->withStatus(503);
});

// $app->get('/image/{client_id:\d+}.jpg', function (Request $request, Response $response, array $args) {
// 	$response->write(file_get_contents('http://lorempixel.com/200/200/people/' . $args['client_id']));
// 	return $response
// 		// ->withHeader('Content-Type', 'image/jpeg')
// 		->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
// });

$app->options('/{routes:.+}', 'cors');
function cors (Request $request, Response $response) { return $response; }