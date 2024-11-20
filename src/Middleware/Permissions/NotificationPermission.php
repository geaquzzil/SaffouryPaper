<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Etq\Restful\Helpers;
use Etq\Restful\Middleware\Permissions\BasePermission;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class NotificationPermission extends BasePermission
{

    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        // parent::__invoke($request, $response, $next);
        $response =  $next($request, $response);
        echo "AFter Response $response";
        return $response;
    }

    public function getAction()
    {
        return "notification";
    }
}
