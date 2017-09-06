<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['determineRouteBeforeAppMiddleware'] = true;

// Create app
$app = new \Slim\App(["settings" => $config]);

$app->add(new \Lib\Middlewares\HeadersMiddleware())
	->add(new \Lib\Middlewares\Error503Middleware())
	->add(new \Tuupola\Middleware\Cors([
		"origin" => ["*"],
		"methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
		"headers.allow" => [],
		"headers.expose" => ['Retry-After'],
		"credentials" => false,
		"cache" => 0,
	]));

// Get container
$container = $app->getContainer();

# Clients
$container['AddClient\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\AddClient\ClientsCtrl($container);
};

# CustomersDetails
$container['CustomersList\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersList\ClientsCtrl($container);
};

# CustomersDetails
$container['CustomersDetails\ClientsCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\ClientsCtrl($container);
};
$container['CustomersDetails\DeptCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\DeptCtrl($container);
};
$container['CustomersDetails\NotesCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\NotesCtrl($container);
};
$container['CustomersDetails\MediaCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\MediaCtrl($container);
};
$container['CustomersDetails\SocialCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\SocialCtrl($container);
};
$container['CustomersDetails\SignatureCtrl'] = function () use ($container) {
	return new \Lib\Controllers\CustomersDetails\SignatureCtrl($container);
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
$app->run();
