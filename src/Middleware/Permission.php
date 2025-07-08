<?php


namespace Etq\Restful\Middleware;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

final class Permission extends Base
{
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        $jwtHeader = $request->getHeaderLine('Authorization');
        if (! $jwtHeader) {
            throw new \Exception('Token required.', ERR_AUTH_REQ);
        }
        $jwt = explode('Bearer ', $jwtHeader);
        if (! isset($jwt[1])) {
            throw new \Exception('Token invalid.', ERR_AUTH_INVAILD);
        }
        $decoded = $this->checkToken($jwt[1]);
        $object = (array) $request->getParsedBody();
        $object['decoded'] = $decoded;

        return $next($request->withParsedBody($object), $response);
    }
}
