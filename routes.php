<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
	return $response->withStatus(401);
});

### Creating Appointment
$app->group('/creating-appointment', function () use ($app) {
	$prefix = 'CreatingAppointment\\';
	$app->get('/clients', $prefix . 'ClientsCtrl:getClients');
	$app->get('/clients/{id:\d+}', $prefix . 'ClientsCtrl:getClient');

	$app->get ('/procedures',   $prefix . 'ProceduresCtrl:getAllProcedures');
	$app->get ('/procedures/bi',   $prefix . 'ProceduresCtrl:getBIProcedures');
	$app->post('/procedures', $prefix . 'ProceduresCtrl:add')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
	$app->post('/appointments', $prefix . 'AppointmentsCtrl:add')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
});

### Clients
$app->group('/add-client/clients', function () use ($app) {
	$prefix = 'AddClient\\ClientsCtrl:';
	$app->get('/{id:\d+}', $prefix . 'getClient');
	$app->get('',    $prefix . 'getClients');
	$app->delete('', $prefix . 'removeUser');
	$app->post  ('', $prefix . 'addClient')->add(new \Lib\Middlewares\PostReturnIDMiddleware());

	$app->get('/media', $prefix . 'getMedia');
});

### Customers List
$app->group('/customers-list/clients', function () use ($app) {
	$prefix = 'CustomersList\ClientsCtrl:';
	$app->get   ('', $prefix . 'getClients');
	$app->delete('', $prefix . 'deleteClients');

	$app->get('/check-phone-number-exists/{number}', $prefix . 'checkPhoneNumberExists');
});

### Customers Details
$app->group('/customers-details/clients', function () use ($app) {
	$prefix = 'CustomersDetails\\';
	$cl_prefix = $prefix . 'ClientsCtrl:';

	$app->get('', $cl_prefix . 'getClients');

	$app->patch('/{client_id:\d+}', $cl_prefix . 'setPersonalData');

	# Dept
	$app->group('/{client_id:\d+}/dept', function () use ($app, $prefix) {
		$dept_prefix = $prefix . 'DeptCtrl';
		$app->post('', $dept_prefix . ':addDept')->add(new \Lib\Middlewares\PostReturnIDMiddleware());

		$app->group('/{dept_id:\d+}', function () use ($app, $dept_prefix) {
			$app->put   ('', $dept_prefix . ':updateDept');
			$app->delete('', $dept_prefix . ':deleteDept');
		});
	});

	# Note
	$app->group('/{client_id:\d+}/notes', function () use ($app, $prefix) {
		$note_prefix = $prefix . 'NotesCtrl';
		$app->post('', $note_prefix . ':addNote')->add(new \Lib\Middlewares\PostReturnIDMiddleware());

		$app->group('/{note_id:\d+}', function () use ($app, $note_prefix) {
			$app->patch ('', $note_prefix . ':updateNote');
			$app->delete('', $note_prefix . ':deleteNote');
		});
	});

	# Map
	$app->get('/{client_id:\d+}/map', $cl_prefix . 'getMap');

	# Media
	$app->group('/{client_id:\d+}/media', function () use ($app, $prefix) {
		$app->post('', 'CustomersDetails\MediaCtrl:addMedia')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
		$app->patch ('/{media_id:\d+}', 'CustomersDetails\MediaCtrl:editMediaNote');
		$app->delete('/{media_id:\d+}', 'CustomersDetails\MediaCtrl:removeMedia');
	});

	# Social
	$app->group('/{client_id:\d+}/social', function () use ($app, $prefix) {
		$app->post('', $prefix . 'SocialCtrl:addSocial')->add(new \Lib\Middlewares\PostReturnIDMiddleware());
		$app->delete('/{media_id:\d+}', $prefix . 'SocialCtrl:deleteSocial');
	});

	# Signature
	$app->group('/{client_id:\d+}/signature', function () use ($app, $prefix) {
		$app->post('', $prefix . 'SignatureCtrl:addSignature');
		$app->delete('', $prefix . 'SignatureCtrl:deleteSignature');
	});

	$app->put('/{client_id:\d+}/send-link-fill-up', $cl_prefix . 'sendLinkFillUpPersonalData');
});

$app->any('/503', function (Request $request, Response $response):Response {
	return $response->withHeader('Retry-After', 120)->withStatus(503);
});

$app->options('/{routes:.+}', 'cors');
function cors (Request $request, Response $response) { return $response; }