<?php

namespace Lib;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
	$response->getBody()->write('GET /');
	return $response;
});

### Creating Appointment
$app->get('/creating-appointment/clients/', 'CreatingAppointment\ClientsCtrl:getClients');
$app->get('/creating-appointment/client/{id}', 'CreatingAppointment\ClientsCtrl:getClient');
$app->get('/creating-appointment/procedures/', 'CreatingAppointment\ProceduresCtrl:getProceduresData');
$app->post('/creating-appointment/appointments/', 'CreatingAppointment\AppointmentsCtrl:saveData');


### Rest API
$app->get('/customers-details/clients/', 'CustomersDetails\ClientsCtrl:getClients');

$app->put('/clients/{client_id:\d+}/vip', 'CustomersDetails\ClientsCtrl:setVip');

# Dept
$app->post('/clients/{client_id:\d+}/dept', 'CustomersDetails\DeptCtrl:addDept');
$app->put('/clients/{client_id:\d+}/dept/{dept_id:\d+}', 'CustomersDetails\DeptCtrl:updateDept');
$app->delete('/clients/{client_id:\d+}/dept/{dept_id:\d+}', 'CustomersDetails\DeptCtrl:deleteDept');