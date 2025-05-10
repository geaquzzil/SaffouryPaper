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


$app->post('/resetPassword', '');
// $app->get('/action_transfer_account', 'Etq\Restful\Controller\ExtensionController:transferAccount');
$app->get('/action_transfer_account', 'Etq\Restful\Controller\ExtensionController:transferAccount');

$app->post('/api/v1/login', \Etq\Restful\Controller\User\Login::class);
$app->group('/api/v1', RouteFromTable::class);
