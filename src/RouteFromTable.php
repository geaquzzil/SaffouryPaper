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
use Etq\Restful\Controller\BlockController;
use Etq\Restful\Controller\CustomerController;
use Etq\Restful\Database\DBBackupAndRestore;
use Etq\Restful\Middleware\Auth;
use Etq\Restful\Middleware\Permissions\UserType;
use Etq\Restful\Repository\DashboardRepository;
use Slim\Http\Request;
use Slim\Http\Response;

class RouteFromTable
{
    const ID_OPTIONAL = '[/[{iD:\d+}]]';

    const ID_REQUIRED = '/{iD:\d+}';

    public function __invoke(\Slim\App $app): void
    {

        $tables = $app->getContainer()['repository']->getAllTablesWithoutView();
        $permissionRep = $app->getContainer()['permission_repository'];
        $r = $this;
        for ($i = 0; $i < count($tables); $i++) {
            // $table = "";
            $table = (string)$tables[$i]["table_name"];
            $app->group('/' . $table, function () use ($app, $table, $permissionRep, $r): void {
                $app->get('', GetAll::class)->add(new ListPermission($permissionRep));
                $app->get(self::ID_REQUIRED, GetOne::class)->add(new ViewPermission($permissionRep));
                // $app->get('/print/{iD}', Task\GetAll::class)->add(new \PrintPermssion($table));
                $app->post('', Create::class)->add(new AddPermission($permissionRep));
                $app->put(self::ID_REQUIRED, Update::class)->add(new EditPermission($permissionRep));
                $app->delete(self::ID_REQUIRED, Delete::class)->add(new DeletePermission($permissionRep));
                $app->get('/dashit[/]', 'Etq\Restful\Controller\DashboardController:dashIT')->add(new ListPermission($permissionRep));
                $app->get('/changed_records[/]', 'Etq\Restful\Controller\DefaultController:getChangedRecords')->add(new ListPermission($permissionRep));
                $app->group(
                    "/not_used",
                    function () use ($app, $permissionRep): void {
                        $app->delete('[/]', 'Etq\Restful\Controller\DashboardController:deleteNotUsedRecords')->add(new Auth(UserType::ADMIN, $permissionRep));
                        $app->get('[/]', 'Etq\Restful\Controller\DashboardController:getNotUsedRecords')->add(new Auth(UserType::EMPLOYEE, $permissionRep));
                    }

                );
                $app->get('/server_data[/]', 'Etq\Restful\Controller\DefaultController:getServerDataByTable')->add(new Auth(UserType::GUEST, $permissionRep));
                $r->addExtensionTableUrl($table, $app);
            });
        }
        $app->post('/resetPassword', '');
        // $app->get('/action_transfer_account', 'Etq\Restful\Controller\ExtensionController:transferAccount');
        $app->get('/action_transfer_account', 'Etq\Restful\Controller\ExtensionController:transferAccount');

        $app->post('/login', \Etq\Restful\Controller\User\Login::class);
        $app->get('/status', 'Etq\Restful\Controller\DefaultController:getStatus')->add(new Auth(UserType::ADMIN, $permissionRep));
        $app->get('/ping', 'Etq\Restful\Controller\DefaultController:getPing');
        $app->get('/', 'Etq\Restful\Controller\DefaultController:getHelp');

        $app->get('/server_data[/]', 'Etq\Restful\Controller\DefaultController:getServerData')->add(new Auth(UserType::GUEST, $permissionRep));
        $app->get('/tables[/]', 'Etq\Restful\Controller\DefaultController:getTables')->add(new Auth(UserType::ADMIN, $permissionRep));
        $app->get('/exchange_rate[/]', ExchangeRateController::class)
            ->add(new StaticPermission("action_exchange_rate", $permissionRep));

        $app->put('/block[/{tableName:[A-Za-z]+}[/{iD:\d+}]]', BlockController::class)
            ->add(new StaticPermission("action_block", $permissionRep));


        $app->put('/unblock[/{tableName:[A-Za-z]+}[/{iD:\d+}]]', BlockController::class . ":unblock")
            ->add(new StaticPermission("action_block", $permissionRep));

        $app->group('/notification', function () use ($app): void {
            $app->post('[/{tableName:[A-Za-z]+}[/{iD:\d+}]]', NotificationController::class . ":send");
            $app->post('/topic/{topicName}', NotificationController::class . ":sendToTopic");
        })->add(new StaticPermission("action_notification", $permissionRep));

        $app->group('/dashboard', function () use ($app, $permissionRep): void {
            $app->get('[/]', 'Etq\Restful\Controller\DashboardController:getDashboard')->add(new Auth(UserType::CUSTOMER, $permissionRep));
            $app->get('/fund[/]', 'Etq\Restful\Controller\DashboardController:getFundDashboard');
            // $app->get('[/]', 'Etq\Restful\Controller\DashboardController:getDashboard');
            // $app->get('/' . EMP . '[/[{iD:\d+}]]', BlockController::class);
        });




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
        })->add(new Auth(UserType::ADMIN, $permissionRep));
    }
    private function addExtensionTableUrl(string $tableName, &$app)
    {

        if (array_key_exists($tableName, $this->getExtessionTableUrl)) {
            $route = $this->getExtessionTableUrl[$tableName];
            foreach ($route as $router) {
                $hasPermission = $router[3];
                if ($hasPermission) {
                    switch ($router[4]) {
                        case ExtenstionPermissionType::BY_STATIC:
                            $app->{$router[2]}($router[0], $router[1])->add(
                                new StaticPermission($hasPermission, $app->getContainer()['permission_repository'])
                            );
                            break;
                        case ExtenstionPermissionType::BY_AUTH:
                            $allowHigherPerm = false;
                            if (is_array($hasPermission)) {
                                $allowHigherPerm = $hasPermission[1];
                                $hasPermission = $hasPermission[0];
                            }
                            $app->{$router[2]}($router[0], $router[1])->add(
                                new Auth($hasPermission, $app->getContainer()['permission_repository'], $allowHigherPerm)
                            );
                            break;
                        case ExtenstionPermissionType::BY_TABLE:

                            $app->{$router[2]}($router[0], $router[1])->add(
                                new StaticPermission($hasPermission, $app->getContainer()['permission_repository'])
                            );
                            break;
                    }
                } else {
                    $app->{$router[2]}($router[0], $router[1]);
                }
            }
        }
    }

    private $getExtessionTableUrl =

    [
        RI => [
            [
                '/overdue[/]',
                'Etq\Restful\Controller\CustomerController:getOverDueReservationInvoice',
                'get',
                RI // new ViewPermission($app->getContainer()['permission_repository'])
                ,
                ExtenstionPermissionType::BY_STATIC

            ],
        ],
        ORDR => [
            [
                '/overdue[/]',
                'Etq\Restful\Controller\CustomerController:getOverDueCustomers',
                'get',
                ORDR // new ViewPermission($app->getContainer()['permission_repository'])
                ,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/nextPayment[/]',
                'Etq\Restful\Controller\CustomerController:getNextPayment',
                'get',
                ORDR,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/currentDayPayment[/]',
                'Etq\Restful\Controller\CustomerController:getCurrentDayPayment',
                'get',
                ORDR,
                ExtenstionPermissionType::BY_STATIC
            ],


            [
                '/profits[/]',
                'Etq\Restful\Controller\CustomerController:getProfits',
                'get',
                ORDR,
                ExtenstionPermissionType::BY_STATIC
            ],
        ],
        PR => [
            [
                '/most_popular[/]',
                'Etq\Restful\Controller\ProductController:getMostPopularProducts',
                'get',
                PR,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/bestSelling[/]',
                'Etq\Restful\Controller\ProductController:getBestSellingProducts',
                'get',
                PR,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/expectedToBuy[/]',
                'Etq\Restful\Controller\ProductController:getExpectedProductsToBuy',
                'get',
                PR,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/movement' . self::ID_REQUIRED,
                'Etq\Restful\Controller\ProductController:getMovement',
                'get',
                PR,
                ExtenstionPermissionType::BY_STATIC
            ],
        ],
        // TYPE => [
        //     // ['availability', 'Etq\Restful\Controller\ProductController:getProductTypeAvailability', 'get', null],

        // ],
        EMP => [
            [
                '/token[/]',
                'Etq\Restful\Controller\CustomerController:updateToken',
                'put',
                [UserType::EMPLOYEE, true],
                ExtenstionPermissionType::BY_AUTH
            ],
            // [
            //     '/notification' . self::ID_OPTIONAL,
            //     NotificationController::class . ":send",
            //     'post',
            //     "action_notification",
            //     ExtenstionPermissionType::BY_STATIC
            // ],

        ],
        CUST => [
            // [
            //     '/notification' . self::ID_OPTIONAL,
            //     NotificationController::class . ":send",
            //     'post',
            //     "action_notification",
            //     ExtenstionPermissionType::BY_STATIC
            // ],
            [
                '/token[/]',
                'Etq\Restful\Controller\CustomerController:updateToken',
                'put',
                UserType::CUSTOMER,
                ExtenstionPermissionType::BY_AUTH
            ],
            [
                '/overdue'     . self::ID_OPTIONAL,
                'Etq\Restful\Controller\CustomerController:getOverDueCustomers',
                'get',
                CUST // new ViewPermission($app->getContainer()['permission_repository'])
                ,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/transfer/{from:\d+}/{to:\d+}',
                CustomerController::class . ":transfer",
                'put',
                "action_transfer_account",
                ExtenstionPermissionType::BY_STATIC
            ],

            [
                '/overdue_reservation'     . self::ID_OPTIONAL,
                'Etq\Restful\Controller\CustomerController:getOverDueReservationInvoice',
                'get',
                CUST // new ViewPermission($app->getContainer()['permission_repository'])
                ,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/nextPayment' . self::ID_OPTIONAL,
                'Etq\Restful\Controller\CustomerController:getNextPayment',
                'get',
                CUST,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/currentDayPayment' . self::ID_OPTIONAL,
                'Etq\Restful\Controller\CustomerController:getCurrentDayPayment',
                'get',
                CUST,
                ExtenstionPermissionType::BY_STATIC
            ],


            [
                '/profits'     . self::ID_OPTIONAL,
                'Etq\Restful\Controller\CustomerController:getProfits',
                'get',
                CUST,
                ExtenstionPermissionType::BY_STATIC
            ],

            [
                '/balance'     . self::ID_OPTIONAL,
                'Etq\Restful\Controller\CustomerController:getBalance',
                'get',
                CUST,
                ExtenstionPermissionType::BY_STATIC
            ],
            [
                '/statement'   . self::ID_REQUIRED,
                'Etq\Restful\Controller\CustomerController:getStatement',
                'get',
                CUST,
                ExtenstionPermissionType::BY_STATIC
            ],
        ],

        // CUT => []

    ];
}
enum ExtenstionPermissionType
{
    case BY_AUTH;
    case BY_STATIC;
    case BY_TABLE;
}
