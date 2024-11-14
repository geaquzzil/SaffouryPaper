<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Etq\Restful\Helpers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class AddPermission extends BasePermission
{
    private string $action = "add";
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        $this->checkForPermission($request, $this->action);




        return $next($request, $response);
    }
}
