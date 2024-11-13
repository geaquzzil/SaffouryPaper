<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;
use Firebase\JWT\JWT;
use \Firebase\JWT\Key;

abstract class BasePermssion
{
    public function __construct() {}
    private $adminID = -1;

    protected function shouldBeSignedIn() {}

    protected function checkToken(string $token): object
    {
        try {
            return JWT::decode(
                $token,
                new Key($_SERVER['SECRET_KEY'], 'HS256')

            );
        } catch (\UnexpectedValueException) {
            throw new \Exception('Forbidden: you are not authorized.', 403);
        }
    }
    public function checkIfSignedIn(
        Request $request,
        Response $response,
        Route $next
    ) {

        $jwtHeader = $request->getHeaderLine('Authorization');



        if (! $jwtHeader) {
            throw new \Exception('Token required.', 400);
            return false;
        }
        $jwt = explode('Bearer ', $jwtHeader);
        if (! isset($jwt[1])) {
            throw new \Exception('Token invalid.', 400);
            return false;
        }
        $decoded = $this->checkToken($jwt[1]);
        //todo path uri
        // print_r($request->getUri()->getPath()); 
        $object = (array) $request->getParsedBody();
        $object['decoded'] = $decoded;

        return true;
    }
    private function isCustomer(int $id)
    {
        return $id > 0;
    }
    private function isGuest(int $id)
    {
        return $id = 0;
    }
    private function isEmployee(int $id)
    {
        return $id < 0;
    }
}
