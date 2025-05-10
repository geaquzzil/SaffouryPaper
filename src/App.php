<?php

namespace Etq\Restful;

use Etq\Restful\Middleware\Permissions\NotificationPermission;

class App
{

    protected $app;

    public function __construct($envFilePath = '')
    {
        # settings
        require_once __DIR__ . '/../src/config.php';

        $dotenv =  \Dotenv\Dotenv::createImmutable($envFilePath);
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT']);

        $settings = require_once __DIR__ . '/../src/Settings.php';
        # app instance
        $app = new \Slim\App($settings);

        # dependencies
        require_once __DIR__ . '/../src/Dependencies.php';

        require_once __DIR__ . '/../src/Repositories.php';


        require_once __DIR__ . '/../src/Extension/Extensions.php';



        require_once __DIR__ . '/Services.php';

        # routes
        require_once __DIR__ . '/../src/Routes.php';

        $this->app = $app;
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
