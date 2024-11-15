<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Etq\Restful\Helpers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class AddPermission extends BasePermission
{

    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        return parent::__invoke($request, $response, $next);
    }

    public function getAction()
    {
        return "add";
    }
}
