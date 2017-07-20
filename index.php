<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$config['displayErrorDetails'] = true;

// Create app
$app = new \Slim\App(["settings" => $config]);

// Get container
$container = $app->getContainer();

# CustomersDetails
$container['CustomersDetails\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\ClientsCtrl($container);
};
$container['CustomersDetails\DeptCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\DeptCtrl($container);
};

# CreatingAppointment
$container['CreatingAppointment\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CreatingAppointment\ClientsCtrl($container);
};
$container['CreatingAppointment\ProceduresCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CreatingAppointment\ProceduresCtrl($container);
};
$container['CreatingAppointment\AppointmentsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CreatingAppointment\AppointmentsCtrl($container);
};

require 'routes.php';
// echo "<pre>";
// print_r($container->router->getRoutes());
// echo "</pre>";
// exit();
$app->run();
