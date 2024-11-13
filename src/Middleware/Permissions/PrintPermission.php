<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class PrintPermssion extends BasePermssion
{
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {


        return $next($request, $response);
    }
}
