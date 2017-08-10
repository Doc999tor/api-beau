<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$cors_settings = [
    "origin" => ["*"],
    "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
    "headers.allow" => [],
    "headers.expose" => ['Retry-After'],
    "credentials" => false,
    "cache" => 0,
];

$app->get('/', function (Request $request, Response $response) {
	return $response->getBody()->write('GET /');
});

### Creating Appointment
$app->group('/creating-appointment', function () use ($app) {
	$prefix = 'CreatingAppointment\\';
	$app->get('/clients', $prefix . 'ClientsCtrl:getClients');
	$app->get('/client/{id}', $prefix . 'ClientsCtrl:getClient');
	$app->get('/procedures', $prefix . 'ProceduresCtrl:getProceduresData');
	$app->post('/appointments', $prefix . 'AppointmentsCtrl:saveData');
});

### Customers List
$app->group('/customers-list/clients', function () use ($app) {
	$app->get   ('', 'CustomersList\ClientsCtrl:getClients');
	$app->delete('', 'CustomersList\ClientsCtrl:deleteClients');
})->add(new \Tuupola\Middleware\Cors($cors_settings));

### Customers Details
$app->group('/customers-details/clients', function () use ($app, $cors_settings) {
	$prefix = 'CustomersDetails\\';
	$cl_prefix = $prefix . 'ClientsCtrl:';

	$app->get('', $cl_prefix . 'getClients');

	$app->group('/{client_id:\d+}', function () use ($app, $cl_prefix) {
		$app->patch('', $cl_prefix . 'setPersonalData');
	})->add(new \Tuupola\Middleware\Cors($cors_settings));

	# Dept
	$app->group('/{client_id:\d+}/dept', function () use ($app, $prefix) {
		$dept_prefix = $prefix . 'DeptCtrl';
		$app->post('', $dept_prefix . ':addDept');

		$app->group('/{dept_id:\d+}', function () use ($app, $dept_prefix) {
			$app->put ('', $dept_prefix . ':updateDept');
			$app->delete('', $dept_prefix . ':deleteDept');
		});
	})->add(new \Tuupola\Middleware\Cors($cors_settings));

	# Map
	$app->get('/{client_id:\d+}/map', $cl_prefix . 'getMap');

	# Media
	$app->group('/{client_id:\d+}/media', function () use ($app, $prefix) {
		$app->post('', 'CustomersDetails\MediaCtrl:addMedia');
		$app->patch ('/{media_id:\d+}', 'CustomersDetails\MediaCtrl:editMediaNote');
		$app->delete('/{media_id:\d+}', 'CustomersDetails\MediaCtrl:removeMedia');
	})->add(new \Tuupola\Middleware\Cors($cors_settings));

	# Social
	$app->group('/{client_id:\d+}/social', function () use ($app, $prefix) {
		$app->post('', 'CustomersDetails\SocialCtrl:addSocial');
		$app->delete('/{media_id:\d+}', 'CustomersDetails\SocialCtrl:deleteSocial');
	})->add(new \Tuupola\Middleware\Cors($cors_settings));
});

$app->options('/{routes:.+}', 'cors')->add(new \Tuupola\Middleware\Cors($cors_settings));
function cors (Request $request, Response $response) { return $response; }