<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class ViewPermission extends BasePermission
{

    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        parent::invoke($request, $response, $next);
        $request = $request->withAttribute('Auth', $this);
        return   $next($request, $response);
    }

    public function getAction()
    {
        return "view";
    }
}
