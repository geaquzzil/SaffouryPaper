<?php

class runSocketServer
{
    var $Address = "localhost:8081";
    function __construct()
    {
        /*
         * ***********************************************
         * the runtime
         * ***********************************************
         */
        // require __DIR__ . '/errorHandler.php';
        // require __DIR__ . '/logToFile.php';
        /*
         * ***********************************************
         * inlcude the core server
         * ***********************************************
         */
        // require __DIR__ . "/getOptions.php";
        require __DIR__ . "/webSocketServer.php";
        /*
         * **********************************************
         *  your backend applications
         * **********************************************
         */
        require __DIR__ . '/resource.php';
        require __DIR__ . '/resourceDefault.php';
        require __DIR__ . '/resourceWeb.php';
        require __DIR__ . '/resourcePHP.php';
    }

    function run()
    {
        $server = new websocketServer($this->Address);
        /*
         * ***********************************************
         * set some server variables
         * ***********************************************
         */
        $server->maxPerIP = 0;   // 0=unlimited 
        $server->maxClients = 0; // 0=unlimited 
        $server->pingInterval = 0; // unit is seconds; 0=no pings to clients
        /*
         * ***********************************************
         * instantiate backend 'applications'
         * ***********************************************
         */
        $resDefault = new resourceDefault();
        $resWeb = new resourceWeb();
        $resPHP = new resourcePHP();
        /*
         * ***********************************************
         * register backend 'applications' with server
         * ***********************************************
         */
        $server->registerResource('/', $resDefault);
        $server->registerResource('/web', $resWeb);
        $server->registerResource('/php', $resPHP);
        /*
         * ***********************************************
         * now start it to have the server handle
         * requests from clients
         * ***********************************************
         */

        $server->Start();
    }
}

/*
 * ***********************************************
 * start 
 * ***********************************************
 */

$t = new s();
if ($t->start()) {
    while ($t->isRunning()) {
        echo ".";
        usleep(100);
    }
    $t->join();
}
class s extends Thread
{
    public function __construct() {}
    public function run()
    {
        (new runSocketServer())->run();
        //do something time consuming
    }
}
