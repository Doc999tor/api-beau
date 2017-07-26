<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

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
});

### Customers Details
$app->group('/customers-details/clients', function () use ($app) {
    $app->get('', 'CustomersDetails\ClientsCtrl:getClients');

    $app->group('/{client_id:\d+}', function () use ($app) {
		$app->patch('', 'CustomersDetails\ClientsCtrl:setPersonalData');
		$app->options('', function (Request $request, Response $response) { return $response; }); # cors
	})->add(new \Lib\Middlewares\CorsMiddleware());

	# Dept
	$app->group('/{client_id:\d+}/dept', function () use ($app) {
		$ctrl = 'CustomersDetails\DeptCtrl';
		$app->post('', $ctrl . ':addDept');

		$app->group('/{dept_id:\d+}', function () use ($app, $ctrl) {
			$app->put ('', $ctrl . ':updateDept');
			$app->delete('', $ctrl . ':deleteDept');

			$app->options('', function (Request $request, Response $response) { return $response; }); # cors
		});
	})->add(new \Lib\Middlewares\CorsMiddleware());

	# Map
	$app->get('/{client_id:\d+}/map', 'CustomersDetails\ClientsCtrl:getMap');

	# Media
	$app->post('/{client_id:\d+}/media', 'CustomersDetails\ClientsCtrl:addMedia');
});