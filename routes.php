<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
	return $response->getBody()->write('GET /');
});

### Creating Appointment
$app->group('/creating-appointment', function () use ($app) {
	$prefix = 'CreatingAppointment/';
	$app->get('/clients', $prefix . 'ClientsCtrl:getClients');
	$app->get('/client/{id}', $prefix . 'ClientsCtrl:getClient');
	$app->get('/procedures', $prefix . 'ProceduresCtrl:getProceduresData');
	$app->post('/appointments', $prefix . 'AppointmentsCtrl:saveData');
});

### Customers List
$app->group('/customers-list', function () use ($app) {
	$app->get('/clients', 'CustomersList\ClientsCtrl:getClients');
});

### Customers Details
$app->group('/customers-details/clients', function () use ($app) {
    $app->get('', 'CustomersDetails\ClientsCtrl:getClients');

    $app->group('{client_id:\d+}', function () use ($app) {
		$app->patch('', 'CustomersDetails\ClientsCtrl:setPersonalData')
			->add(new \Lib\Middlewares\CorsMiddleware());
		$app->options('', 'enable_cors'); # cors
	});

	# Dept
	$app->group('/{client_id:\d+}/dept/{dept_id:\d+}', function () use ($app) {
		$ctrl = 'CustomersDetails\DeptCtrl';
		$app->post('', $ctrl . ':addDept');
		$app->put ('', $ctrl . ':updateDept')->add(new \Lib\Middlewares\CorsMiddleware());
		$app->delete('', $ctrl . ':deleteDept')->add(new \Lib\Middlewares\CorsMiddleware());

		$app->options('', 'enable_cors'); # cors
	});

	# Map
	$app->get('/{client_id:\d+}/map', 'CustomersDetails\ClientsCtrl:getMap');
});

function enable_cors (Request $request, Response $response) {
	return $response
		->withHeader('Access-Control-Allow-Methods', 'PUT,PATCH,DELETE')
		->withHeader('Access-Control-Allow-Origin', '*');
}
