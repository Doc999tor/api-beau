<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Slim\Container as Container;

class Controller {
	protected $container;
	protected $time_start;
	function __construct(Container $container) {
		$this->container = $container;
		$time_start = microtime(true);
	}

	public function __get($property) {
		if ($this->container->{$property}) {
			return $this->container->{$property};
		}
	}
}