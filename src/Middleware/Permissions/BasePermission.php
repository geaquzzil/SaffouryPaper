<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

abstract class BasePermssion
{
    public function __construct(protected string $tableName) {}
}
