<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Etq\Restful\Helpers;
use Etq\Restful\Middleware\Permissions\BasePermission;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class StaticPermission extends BasePermission
{


    public function __construct(protected string $permission_action, $repo)
    {
        parent::__construct($repo);
    }


    public function __invoke(
        Request $request,
        Response $response,
        $next
    ) {

        parent::invoke($request, $response, $next);
        $response =  $next($request, $response);
        return $response;
    }

    public function getAction()
    {
        return "view";
    }

    protected function getTableName($request)
    {
        return $this->permission_action;
    }
}
