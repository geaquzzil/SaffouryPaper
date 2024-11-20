<?php

namespace Etq\Restful;

use Etq\Restful\Repository\Repository;
// use Etq\Restful\Middleware\Auth;
use Etq\Restful\Middleware\Permissions\ListPermission;
use Etq\Restful\Middleware\Permissions\AddPermission;
use Etq\Restful\Middleware\Permissions\DeletePermission;
use Etq\Restful\Middleware\Permissions\EditPermission;
use Etq\Restful\Middleware\Permissions\PrintPermission;
use Etq\Restful\Middleware\Permissions\ViewPermission;
use Etq\Restful\Controller\Default\Create;
use Etq\Restful\Controller\Default\Delete;
use Etq\Restful\Controller\Default\GetAll;
use Etq\Restful\Controller\Default\GetOne;
use Etq\Restful\Controller\Default\Update;

class RouteFromTable
{
    private $repo;
    public function sod($app)
    {
        // $table="";
        // ListPermission l=

        // $app->get('', GetAll::class)->add(new ListPermission($table));
    }
    public function __invoke($app): void
    {

        $tables = $app->getContainer()['repository']->getAllTables();
        $permissionRep = $app->getContainer()['permission_repository'];

        for ($i = 0; $i < count($tables); $i++) {
            // $table = "";

            $table = (string)$tables[$i]["table_name"];

            $app->group("/" . $table, function () use ($app): void {
                $app->get('', GetAll::class)->add(new ListPermission($app->getContainer()['permission_repository']));
                $app->get('/{iD}', GetOne::class)->add(new ViewPermission($app->getContainer()['permission_repository']));
                // $app->get('/print/{iD}', Task\GetAll::class)->add(new \PrintPermssion($table));
                $app->post('', Create::class)->add(new AddPermission($app->getContainer()['permission_repository']));
                $app->put('/{iD}', Update::class)->add(new EditPermission($app->getContainer()['permission_repository']));
                $app->delete('/{iD}', Delete::class)->add(new DeletePermission($app->getContainer()['permission_repository']));
            });
        }
    }
}
