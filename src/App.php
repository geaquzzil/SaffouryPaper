<?php

namespace Etq\Restful;


class App
{

    protected $app;

    public function __construct($envFilePath = '')
    {
        # settings


        $dotenv =  \Dotenv\Dotenv::createImmutable($envFilePath);
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT']);

        $settings = require __DIR__ . '/../src/Settings.php';
        # app instance
        $app = new \Slim\App($settings);

        # dependencies
        require __DIR__ . '/../src/Dependencies.php';
        # routes
        require __DIR__ . '/../src/Routes.php';

        $this->app = $app;

        $this->setUpDatabaseManager();
        // $this->setUpDatabaseSchema();
    }
    /**
     * Setup Eloquent ORM.
     */
    private function setUpDatabaseManager()
    {
        # Register the database connection with Eloquent
        $capsule = $this->app->getContainer()->get('db');
    }



    /**
     * Get an instance of the application.
     *
     * @return \Slim\App
     */
    public function get()
    {
        return $this->app;
    }
}