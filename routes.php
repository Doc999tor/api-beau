<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
	return $response->getBody()->write('GET /');
});

### Creating Appointment
$app->group('/creating-appointment', function () use ($app) {
	$app->get('/clients', 'CreatingAppointment\ClientsCtrl:getClients');
	$app->get('/client/{id}', 'CreatingAppointment\ClientsCtrl:getClient');
	$app->get('/procedures', 'CreatingAppointment\ProceduresCtrl:getProceduresData');
	$app->post('/appointments', 'CreatingAppointment\AppointmentsCtrl:saveData');
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
		// exit();
		$app->post('', 'CustomersDetails\DeptCtrl:addDept');
		$app->put('', 'CustomersDetails\DeptCtrl:updateDept')->add(new \Lib\Middlewares\CorsMiddleware());
		$app->delete('', 'CustomersDetails\DeptCtrl:deleteDept')->add(new \Lib\Middlewares\CorsMiddleware());

		$app->options('', 'enable_cors'); # cors
	});

	# Map
	$app->get('/{client_id:\d+}/map', 'CustomersDetails\ClientsCtrl:getMap');
})->add(new \Lib\Middlewares\Error503Middleware());

function enable_cors (Request $request, Response $response) {
	return $response
		->withHeader('Access-Control-Allow-Methods', 'PUT,PATCH,DELETE')
		->withHeader('Access-Control-Allow-Origin', '*');
}
