<?php



if (PHP_SAPI == 'cli-server') {
    # To help the built-in PHP dev server, check if the request was actually for
    # something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

# set timezone for timestamps etc
// date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'vendor/composer', 'vendor/autoload.php']);

use Etq\Restful\App;

$baseDir = __DIR__ . '/../';
# getting instance of app
$app = (new App($baseDir))->get();

# Run app
$app->run();
