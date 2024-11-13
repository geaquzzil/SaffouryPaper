<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class PrintPermission extends BasePermission
{
    private string $action = "print";
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {


        return $next($request, $response);
    }
}
