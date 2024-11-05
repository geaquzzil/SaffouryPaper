<?php
class SocketThread extends Thread
{
    public function __construct()
    {
        require __DIR__ . "/server.php";
    }
    public function run()
    {
        (new runSocketServer())->run();
        //do something time consuming
    }
}
