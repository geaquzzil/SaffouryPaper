<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/status', 'Etq\Restful\Controller\DefaultController:getStatus');
$app->get('/', 'Etq\Restful\Controller\DefaultController:getHelp');
// $app->post('/login', \App\Controller\User\Login::class);
$app->get('/{tableName}', function (Request $req, Response $res, array $args) {

    $queryParams = $req->getQueryParams();
    $tableName = $args["tableName"];

    if ($tableName == null) {
    }

    // $objcets = null;
    // $details = null;

    // $objects = $queryParams['objectTables'];
    // $details = $queryParams['detailTables'];

    // return $res;
    // echo " ds" . ($req);

    // print_r($queryParams);

    // $options = getOptions();
    // $res->getBody()->write($args["tableName"]);
    // return $res;

    // $data = depthSearch(null, $tableName, 1, [], [], $options);
    // print_r($data);


    $data = array('name' => 'Bob', 'age' => 40);
    $payload = json_encode($data);

    $res->getBody()->write(($payload));
    return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
});
