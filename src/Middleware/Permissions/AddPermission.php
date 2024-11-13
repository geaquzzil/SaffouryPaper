<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class AddPermssion extends BasePermssion
{
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {

        // $next->
        print_r($request->getUri()->getPath());

        return $next($request, $response);
    }
}
