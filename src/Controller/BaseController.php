<?php

namespace Etq\Restful\Controller;

use Etq\Restful\Helpers;
use Exception;
use Slim\Container;
use Slim\Http\Response;
use Slim\Http\Request;

abstract class BaseController
{


    protected string $tableName;

    public function __construct(protected Container $container) {}

    protected function checkForID(array $args)
    {
        if (empty($args)) {
            throw new Exception("iD not found");
        }
        if (!isset($args["iD"])) {
            throw new Exception("iD not found");
        }

        if (!ctype_digit($args['iD'])) {
            throw new Exception("Expect iD to be integer");
        }
        return   (int) $args['iD'];
    }
    protected function textResponse(
        Response $response,
        string $message,

    ) {
        return $response->withHeader('Content-Type', 'text/plain')->write($message);
    }
    protected function jsonResponse(
        Response $response,
        string $status,
        $message,
        int $code
    ): Response {
        $result = [
            'code' => $code,
            'status' => $status,
            'message' => $message,
        ];

        return $response->withJson($result, $code, JSON_PRETTY_PRINT);
    }

    protected static function isRedisEnabled(): bool
    {
        return filter_var($_SERVER['REDIS_ENABLED'], FILTER_VALIDATE_BOOLEAN);
    }

    protected function init(Request $request)
    {
        $this->tableName  = Helpers::explodeURI($request->getUri()->getPath());
    }
}
