<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class ViewPermission extends BasePermission
{

    private string $action = "view";
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {

        $this->checkForPermission($request, $this->action);
        return $next($request, $response);
    }
}
