<?php

namespace Etq\Restful;

// use Etq\Restful\Repository\Repository;
// use Etq\Restful\Middleware\Auth;
use Etq\Restful\Middleware\Permissions\ListPermission;
use Etq\Restful\Middleware\Permissions\AddPermssion;
use Etq\Restful\Middleware\Permissions\DeletePermssion;
use Etq\Restful\Middleware\Permissions\EditPermssion;
use Etq\Restful\Middleware\Permissions\PrintPermssion;
use Etq\Restful\Middleware\Permissions\ViewPermssion;
use Etq\Restful\Controller\Default\Create;
use Etq\Restful\Controller\Default\Delete;
use Etq\Restful\Controller\Default\GetAll;
use Etq\Restful\Controller\Default\GetOne;
use Etq\Restful\Controller\Default\Update;

class RouteFromTable
{

    public function sod($app)
    {
        // $table="";

        // $app->get('', GetAll::class)->add(new ListPermission($table));
    }
    public function __invoke($app): void
    {
        $tables = $app->container['repository']->getAllTables();

        for ($i = 0; $i < count($tables); $i++) {
            // $table = "";

            $table = (string)$tables[$i]["table_name"];

            $app->group($table, function () use ($app): void {
                $app->get('', GetAll::class)->add(new ListPermission());
                // $app->get('/print/{iD}', Task\GetAll::class)->add(new \PrintPermssion($table));
                $app->post('', Create::class)->add(new AddPermssion());
                $app->get('/{iD}', GetOne::class)->add(new ViewPermssion());
                $app->put('/{iD}', Update::class)->add(new EditPermssion());
                $app->delete('/{iD}', Delete::class)->add(new DeletePermssion());
            });
        }
    }
}
