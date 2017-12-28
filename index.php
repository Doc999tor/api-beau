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

// Register component on container
$container['view'] = function ($c) {
	$view = new \Slim\Views\Twig('views', [
		'cache' => false
	]);
	$view->addExtension(new \Slim\Views\TwigExtension(
		$c['router'],
		$c['request']->getUri()
	));

	return $view;
};

require 'controllers.php';
require 'routes.php';
$app->run();
