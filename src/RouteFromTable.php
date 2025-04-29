<?php

namespace Etq\Restful;

use Etq\Restful\Controller\Admin\DatabaseController;
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
use Etq\Restful\Controller\ExchangeRateController;
use Etq\Restful\Controller\NotificationController;
use Etq\Restful\Database\DBBackupAndRestore;
use Etq\Restful\Middleware\Auth;
use Etq\Restful\Middleware\Permissions\UserType;
use Slim\Http\Request;
use Slim\Http\Response;

class RouteFromTable
{
    const ID_OPTIONAL = '[/[{iD:\d+}]]';

    const ID_REQUIRED = '/{iD:\d+}';
    public function __invoke($app): void
    {

        $tables = $app->getContainer()['repository']->getAllTablesWithoutView();
        $permissionRep = $app->getContainer()['permission_repository'];
        $r = $this;

        for ($i = 0; $i < count($tables); $i++) {
            // $table = "";

            $table = (string)$tables[$i]["table_name"];

            $app->group('/' . $table, function () use ($app, $table, $permissionRep, $r): void {
                $app->get('', GetAll::class)->add(new ListPermission($permissionRep));
                $app->get('/{iD:[0-9]+}', GetOne::class)->add(new ViewPermission($permissionRep));
                // $app->get('/print/{iD}', Task\GetAll::class)->add(new \PrintPermssion($table));
                $app->post('', Create::class)->add(new AddPermission($permissionRep));
                $app->put('/{iD:[0-9]+}', Update::class)->add(new EditPermission($permissionRep));
                $app->delete('/{iD:[0-9]+}', Delete::class)->add(new DeletePermission($permissionRep));
                // echo " sad";
                $r->addExtensionTableUrl($table, $app);
            });
        }
        // $this->getRouters($app);
        // $app->group('/token', function () use ($app): void {
        //     $app->post('/{iD:[0-9]+', '');
        //     $app->put('/{iD:[0-9]+', '');
        // });
        $app->group('/database', function () use ($app): void {

            $res = array();
            // print_r($res);
            $res['Content-Description'] = 'File Transfer';
            $res['Content-Type'] = 'application/octet-stream';
            // $res['Content-Disposition'] = 'attachment; filename=' . basename($zipname);
            $res['Content-Transfer-Encoding'] = 'binary';
            $res['Expires'] = '0';
            $res['Cache-Control'] = 'must-revalidate';
            $res['Pragma'] = 'public';
            // $res['Content-Length'] = filesize($zipname);
            $app->post('/backup[/]',    DatabaseController::class);
            $app->post('/restore[/]', DatabaseController::class);
        })->add(new Auth(UserType::ADMIN));

        $app->group('/notification', function () use ($app): void {
            $app->get('[/]', NotificationController::class);
            $app->get('/' . CUST . '[/[{iD:\d+}]]', NotificationController::class);

            $app->get('/' . EMP . '[/[{iD:\d+}]]', NotificationController::class);
        })->add(new StaticPermission("action_notification", $permissionRep));


        $app->group('/block', function () use ($app): void {
            $app->get('[/]', BlockController::class);
            $app->get('/' . CUST . '[/[{iD:\d+}]]', BlockController::class);

            $app->get('/' . EMP . '[/[{iD:\d+}]]', BlockController::class);
        })->add(new StaticPermission("action_block", $permissionRep));

        $app->group('/transfer', function () use ($app): void {
            //TODO args from & to
            $app->get('/' . CUST, TransferController::class);
            //TODO money or account

            // $app->get('/' . EMP . '[/[{iD:\d+}]]', BlockController::class);
        })->add(new StaticPermission("action_transfer_account", $permissionRep));



        $app->group('/dashboard', function () use ($app): void {
            //TODO args from & to

            $app->get('/' . CUST, TransferController::class);

            // $app->get('/' . EMP . '[/[{iD:\d+}]]', BlockController::class);
        })->add(new StaticPermission("action_transfer_account", $permissionRep));


        // $app->group('/transfer', function () use ($app): void {
        //     //TODO args from & to
        //     $app->get('/customer_account', TransferController::class);
        //     $app->get('/money', TransferController::class);

        //     // $app->get('/' . EMP . '[/[{iD:\d+}]]', BlockController::class);
        // })->add(new StaticPermission("action_transfer_account", $app->getContainer()['permission_repository']));

        $app->get('/tables[/]', 'Etq\Restful\Controller\DefaultController:getTabels')->add(new Auth(UserType::ADMIN));
        $app->get('/server_data[/]', '');
        $app->get('/exchange_rate[/]', ExchangeRateController::class)
            ->add(new StaticPermission("action_exchange_rate", $permissionRep));
    }
    private function addExtensionTableUrl(string $tableName, &$app)
    {

        if (array_key_exists($tableName, $this->getExtessionTableUrl)) {
            $route = $this->getExtessionTableUrl[$tableName];
            foreach ($route as $router) {


                // echo "\n this is  $router[0] $router[1] $router[2] $router[3] \n";

                // print_r($route);
                // $func = $GLOBALS["CUSTOM_SEARCH_QUERY"][$objectName];
                // if (is_callable($func)) {
                //     $hasCustomFunctionFounded = true;
                //     return $func($object);
                // }
                $app->{$router[2]}($router[0], $router[1]);
                $hasPermission = $router[3];
                if ($hasPermission) {
                    // echo "hasPermssion:$hasPermission \n";
                    $app->add(new StaticPermission($hasPermission, $app->getContainer()['permission_repository']));
                }
            }
            // echo $search_array[20120504];
        }
    }

    private $getExtessionTableUrl =
    [
        // PR => [
        //     ['movement[[/]{iD:\d+}]', 'Etq\Restful\Controller\ProductController:getMovement', 'get', null],
        //     ['most_popular', 'Etq\Restful\Controller\ProductController:getMostPopular', 'get', null],
        //     //TODO should i deprecated
        //     ['search', 'Etq\Restful\Controller\ProductController:searchForProduct', 'get', null],
        //     ['similar/{iD:\d+}', 'Etq\Restful\Controller\ProductController:getSimilar', 'get', null],
        // ],
        // TYPE => [
        //     ['availability', 'Etq\Restful\Controller\ProductTypeController:getAvailability', 'get', null]
        // ],
        // EMP => [
        //     // ['token/{iD:\d+}', 'Etq\Restful\Controller\EmployeeController:createToken', 'post', null],
        //     // ['token/{iD:\d+}', 'Etq\Restful\Controller\EmployeeController:updateToken', 'put', null],
        // ],
        CUST => [
            // ['token/{iD:\d+}', 'Etq\Restful\Controller\CustomerController:createToken', 'post', null],
            // ['token/{iD:\d+}', 'Etq\Restful\Controller\CustomerController:updateToken', 'put', null],
            ['/statement'   . self::ID_REQUIRED, 'Etq\Restful\Controller\CustomerController:getStatement', 'get', null],
            ['/terms'       . self::ID_OPTIONAL, 'Etq\Restful\Controller\CustomerController:getTerms', 'get', null],
            ['/profits'     . self::ID_OPTIONAL, 'Etq\Restful\Controller\CustomerController:getProfits', 'get', null],
            ['/notPaid'     . self::ID_OPTIONAL, 'Etq\Restful\Controller\CustomerController:getNotPaidCustomers', 'get', null],
            ['/overdue'     . self::ID_OPTIONAL, 'Etq\Restful\Controller\CustomerController:getOverDueCustomers', 'get', null],
            ['/balance'     . self::ID_OPTIONAL, 'Etq\Restful\Controller\CustomerController:getBalance', 'get', null],
            ['/nextPayment' . self::ID_OPTIONAL, 'Etq\Restful\Controller\CustomerController:getNextPayment', 'get', null],
            ['/currentDayPayment' . self::ID_OPTIONAL, 'Etq\Restful\Controller\CustomerController:getCurrentDayPayment', 'get', null],
        ],

        // CUT => []

    ];
}
