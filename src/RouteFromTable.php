<?php

namespace Etq\Restful;

use Etq\Restful\Controller\DatabaseController;
use Etq\Restful\Repository\Repository;
// use Etq\Restful\Middleware\Auth;
use Etq\Restful\Middleware\Permissions\ListPermission;
use Etq\Restful\Middleware\Permissions\AddPermission;
use Etq\Restful\Middleware\Permissions\DeletePermission;
use Etq\Restful\Middleware\Permissions\EditPermission;
use Etq\Restful\Middleware\Permissions\StaticPermission;
use Etq\Restful\Middleware\Permissions\PrintPermission;
use Etq\Restful\Middleware\Permissions\ViewPermission;
use Etq\Restful\Controller\Default\Create;
use Etq\Restful\Controller\Default\Delete;
use Etq\Restful\Controller\Default\GetAll;
use Etq\Restful\Controller\Default\GetOne;
use Etq\Restful\Controller\Default\Update;
use Etq\Restful\Controller\NotificationController;
use Etq\Restful\Middleware\Auth;
use Etq\Restful\Middleware\Permissions\UserType;

class RouteFromTable
{
    private $repo;

    public function sod($app) {}
    public function __invoke($app): void
    {

        $tables = $app->getContainer()['repository']->getAllTables();
        $permissionRep = $app->getContainer()['permission_repository'];

        for ($i = 0; $i < count($tables); $i++) {
            // $table = "";

            $table = (string)$tables[$i]["table_name"];

            $app->group('/' . $table, function () use ($app): void {
                $app->get('', GetAll::class)->add(new ListPermission($app->getContainer()['permission_repository']));
                $app->get('/{iD:[0-9]+}', GetOne::class)->add(new ViewPermission($app->getContainer()['permission_repository']));
                // $app->get('/print/{iD}', Task\GetAll::class)->add(new \PrintPermssion($table));
                $app->post('', Create::class)->add(new AddPermission($app->getContainer()['permission_repository']));
                $app->put('/{iD:[0-9]+}', Update::class)->add(new EditPermission($app->getContainer()['permission_repository']));
                $app->delete('/{iD:[0-9]+}', Delete::class)->add(new DeletePermission($app->getContainer()['permission_repository']));
            });
        }

        $app->group('/database', function () use ($app): void {
            $app->post('/backup', DatabaseController::class);
            $app->post('/restore', DatabaseController::class);
        })->add(new Auth(UserType::ADMIN));

        // $app->group('/notification', function () use ($app): void {
        //     $app->get('[/]', NotificationController::class);
        //     $app->get('/' . CUST . '[/[{iD:\d+}]]', NotificationController::class);

        //     $app->get('/' . EMP . '[/[{iD:\d+}]]', NotificationController::class);
        // })->add(new StaticPermission("action_notification", $app->getContainer()['permission_repository']));


        // $app->group('/block', function () use ($app): void {
        //     $app->get('[/]', BlockController::class);
        //     $app->get('/' . CUST . '[/[{iD:\d+}]]', BlockController::class);

        //     $app->get('/' . EMP . '[/[{iD:\d+}]]', BlockController::class);
        // })->add(new StaticPermission("action_block", $app->getContainer()['permission_repository']));

        // $app->group('/transfer', function () use ($app): void {
        //     //TODO args from & to
        //     $app->get('/' . CUST, TransferController::class);

        //     // $app->get('/' . EMP . '[/[{iD:\d+}]]', BlockController::class);
        // })->add(new StaticPermission("action_transfer_account", $app->getContainer()['permission_repository']));



        // $app->group('/transfer', function () use ($app): void {
        //     //TODO args from & to
        //     $app->get('/customer_account', TransferController::class);
        //     $app->get('/money', TransferController::class);

        //     // $app->get('/' . EMP . '[/[{iD:\d+}]]', BlockController::class);
        // })->add(new StaticPermission("action_transfer_account", $app->getContainer()['permission_repository']));


        // $app->get('/exchange_rate[/]', ExchangeRateController::class)
        //     ->add(new StaticPermission("action_exchange_rate", $app->getContainer()['permission_repository']));
    }


    private function addExtensionTableUrl(string $tableName) {}

    private $getExtessionTableUrl =
    [
        PR => [
            ['movement/{iD:\d+}' => 'Etq\Restful\Controller\ProductController:getMovement'],
            ['most_popular' => 'Etq\Restful\Controller\ProductController:getMostPopular'],
            ['similar/{iD:\d+}' => 'Etq\Restful\Controller\ProductController:getSimilar'],
            ['similar' => 'Etq\Restful\Controller\ProductController:getSimilar'],
        ],
        TYPE => [
            ['availability']
        ],
        CUST => [
            ['terms[/[{iD:\d+}]]' => ''],
            ['profits[/[{iD:\d+}]]' => ''],
            ['notPaid[/[{iD:\d+}]]' => ''],
            ['statement/{iD:\d+}' => ''],
            ['balance[/[{iD:\d+}]]' => ''],
            ['nextPayment[/[{iD:\d+}]]' => ''],
        ],
        CUT => []

    ];
}
