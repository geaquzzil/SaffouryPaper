<?php

namespace Etq\Restful\Middleware\Permissions;

use Etq\Restful\Helpers;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class ListPermission extends BasePermission
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
        return "list";
    }
}
