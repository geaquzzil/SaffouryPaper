<?php

namespace Etq\Restful\Middleware\Permissions;

use Etq\Restful\Helpers;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class ListPermission extends BasePermission
{
    private string $action = "list";
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {

        $this->checkForPermission($request, $this->action);

        // // print_r($request->getUri()->getPath());
        // $tableName = Helpers::explodeURI($request->getUri()->getPath());
        // $this->repo->getPermission(0, $tableName, true);
        // print_r($this->repo->getPermission(0, $tableName));

        // $jwtHeader = $request->getHeaderLine('Authorization');

        // if (! $jwtHeader) {
        //     throw new \Exception('Token required.', code: 400);
        // }
        // $jwt = explode('Bearer ', $jwtHeader);
        // if (! isset($jwt[1])) {
        //     throw new \Exception('Token invalid.', 400);
        // }
        // $decoded = $this->checkToken($jwt[1]);
        // //todo path uri
        // // print_r($request->getUri()->getPath()); 
        // $object = (array) $request->getParsedBody();
        // $object['decoded'] = $decoded;

        // return $next($request->withParsedBody($object), $response);


        // echo "ListPErmission ";
        // echo $this->action;


        return $next($request, $response);
    }
}
