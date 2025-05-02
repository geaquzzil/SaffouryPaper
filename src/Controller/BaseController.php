<?php

namespace Etq\Restful\Controller;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\Options;
use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\SearchRepository;
use Exception;
use Slim\Container;
use Slim\Http\Response;
use Slim\Http\Request;

abstract class BaseController
{


    protected string $tableName;
    protected Options $options;

    public function __construct(protected Container $container) {}


    protected function checkForOptionalID(array $args)
    {
        if (!isset($args["iD"])) {
            return null;
        }

        if (!ctype_digit($args['iD'])) {
            return null;
        }
        return   (int) $args['iD'];
    }
    protected function checkForID(array $args)
    {
        if (empty($args)) {
            throw new Exception("iD not found");
        }
        if (!isset($args["iD"])) {
            throw new Exception("iD not found");
        }

        if (!ctype_digit($args['iD'])) {
            throw new Exception("Expect iD to be number");
        }
        return   (int) $args['iD'];
    }
    protected function getRepository(): Repository
    {
        return $this->container['repository'];
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
        int $code,
        ?bool  $withHeder = true
    ): Response {

        $result = [
            'code' => $code,
            'status' => $status,
            'message' => $message,
        ];

        return $response->withJson($withHeder ? $result : $message, $code, JSON_NUMERIC_CHECK);
    }

    protected static function isRedisEnabled(): bool
    {
        return filter_var($_SERVER['REDIS_ENABLED'], FILTER_VALIDATE_BOOLEAN);
    }

    protected function init(Request $request)
    {
        $this->tableName  = Helpers::explodeURIGetTableName($request->getUri()->getPath());
        $this->options = new Options($request);
        $this->options->searchRepository = new SearchRepository($this->container->get("db"), $this->container);
    }

    
}
