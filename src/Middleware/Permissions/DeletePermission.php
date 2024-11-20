<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class DeletePermission extends BasePermission
{
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        parent::invoke($request, $response, $next);

        $respo =   $next($request, $response);
        $this->checkToSendNotification($request, $respo);


        return $respo;
    }

    public function getAction()
    {
        return "delete";
    }
}
