<?php

namespace Lib\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GroupsCtrl extends Controller {
	public function getGroupClients (Request $request, Response $response, array $args):Response {
		return $response->withJson(CustomersList::generateClients(mt_rand(0, 30)));
	}
}