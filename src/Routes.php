<?php

use Etq\Restful\Middleware\Auth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Etq\Restful\Repository;

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
use Etq\Restful\Controller\NotificationController;
use Etq\Restful\Middleware\Permissions\UserType;
use Etq\Restful\RouteFromTable;

$app->get('/status', 'Etq\Restful\Controller\DefaultController:getStatus')->add(new Auth(UserType::ADMIN));
$app->get('/ping', 'Etq\Restful\Controller\DefaultController:getPing');
$app->get('/', 'Etq\Restful\Controller\DefaultController:getHelp');
$app->get('/tables', 'Etq\Restful\Controller\DefaultController:getTabels');

$app->post('/resetPassword', '');
// $app->get('/action_transfer_account', 'Etq\Restful\Controller\ExtensionController:transferAccount');
$app->get('/action_transfer_account', 'Etq\Restful\Controller\ExtensionController:transferAccount');

$app->post('/api/v1/login', \Etq\Restful\Controller\User\Login::class);



$app->group('/api/v1', RouteFromTable::class);
// $app->group('/api/v1', function () use ($app): void {
//     $tables = $app->getContainer()['repository']->getAllTables();

//     for ($i = 0; $i < count($tables); $i++) {
//         // $table = "";

//         $table = "";

//         $app->group("/" . $table, function () use ($app): void {
//             $app->get('', GetAll::class)->add(new ListPermission());
//             // $app->get('/print/{iD}', Task\GetAll::class)->add(new \PrintPermssion($table));
//             $app->post('', Create::class)->add(new AddPermission());
//             $app->get('/{iD}', GetOne::class)->add(new ViewPermission());
//             $app->put('/{iD}', Update::class)->add(new EditPermission());
//             $app->delete('/{iD}', Delete::class)->add(new DeletePermission());
//         });
//     }
// });

// $app->group('/api/v1', function () use ($app): void {


    //add authintication if no authintication found set default auth
    //add permission 
//     $app->group('/tasks', function () use ($app): void {
//         $app->get('', Task\GetAll::class);
//         $app->post('', Task\Create::class);
//         $app->get('/{id}', Task\GetOne::class);
//         $app->put('/{id}', Task\Update::class);
//         $app->delete('/{id}', Task\Delete::class);
//     })->add(new Auth());

//     $app->group('/users', function () use ($app): void {
//         $app->get('', User\GetAll::class)->add(new Auth());
//         $app->post('', User\Create::class);
//         $app->get('/{id}', User\GetOne::class)->add(new Auth());
//         $app->put('/{id}', User\Update::class)->add(new Auth());
//         $app->delete('/{id}', User\Delete::class)->add(new Auth());
//     });

//     $app->group('/notes', function () use ($app): void {
//         $app->get('', Note\GetAll::class);
//         $app->post('', Note\Create::class);
//         $app->get('/{id}', Note\GetOne::class);
//         $app->put('/{id}', Note\Update::class);
//         $app->delete('/{id}', Note\Delete::class);
//     });
// });
// $app->post('/login', \App\Controller\User\Login::class);
// $app->get('/{tableName}', function (Request $req, Response $res, array $args) {

//     $queryParams = $req->getQueryParams();
//     $tableName = $args["tableName"];

//     if ($tableName == null) {
//     }

//     // $objcets = null;
//     // $details = null;

//     // $objects = $queryParams['objectTables'];
//     // $details = $queryParams['detailTables'];

//     // return $res;
//     // echo " ds" . ($req);

//     // print_r($queryParams);

//     // $options = getOptions();
//     // $res->getBody()->write($args["tableName"]);
//     // return $res;

//     // $data = depthSearch(null, $tableName, 1, [], [], $options);
//     // print_r($data);


//     $data = array('name' => 'Bob', 'age' => 40);
//     $payload = json_encode($data);

//     $res->getBody()->write(($payload));
//     return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
// });


// $myvar1 = $req->getParam('myvar'); //checks both _GET and _POST [NOT PSR-7 Compliant]
//     $myvar2 = $req->getParsedBody()['myvar']; //checks _POST  [IS PSR-7 compliant]
//     $myvar3 = $req->getQueryParams()['myvar']; //checks _GET [IS PSR-7 compliant]