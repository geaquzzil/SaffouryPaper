<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class PrintPermission extends BasePermission
{
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        parent::invoke($request, $response, $next);
        return   $next($request, $response);
    }

    public function getAction()
    {
        return "print";
    }
}
