<?php

namespace Etq\Restful;

use Etq\Restful\Repository\Repository;
// use Etq\Restful\Middleware\Auth;
use Etq\Restful\Middleware\Permissions;

class RouteFromTable
{
    public function __invoke($app): void
    {
        $tables = $app->container['repository']->getAllTables();

        for ($i = 0; $i < count($tables); $i++) {
            $table = $tables[$i]["table_name"];

            $app->group($table, function () use ($app): void {
                $app->get('', Task\GetAll::class)->add(new ListPermssion($table));
                $app->get('/print/{iD}', Task\GetAll::class)->add(new \PrintPermssion($table));
                $app->post('', Task\Create::class)->add(new \AddPermssion($table));
                $app->get('/{iD}', Task\GetOne::class)->add(new ViewPermssion($table));
                $app->put('/{iD}', Task\Update::class)->add(new EditPermssion($table));
                $app->delete('/{iD}', Task\Delete::class)->add(new DeletePermssion($table));
            });
        }
    }
}
