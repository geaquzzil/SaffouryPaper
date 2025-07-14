<?php



# DIC configuration
use Etq\Restful\Service\RedisService;
use Etq\Restful\Handler\ApiError;

$container = $app->getContainer();

# custom notFoundHandler to deal with no found routes
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $r = $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');
        return $r;
    };
};

# view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

# monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['db'] = function ($container) {
    $database = $container->get('settings')['db'];
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;port=%s;charset=utf8',
        $database['host'],
        $database['name'],
        $database['port']
    );
    try {
        $pdo = new PDO($dsn, $database['user'], $database['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        // $pdo->exec("set names utf8");

        return $pdo;
    } catch (PDOException $e) {
        throw  new Exception($e->getMessage(), $e->getCode(), $e);
    }
};
# database
$container['capsule'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $settings = $container->get('settings')['db'];
    $capsule->addConnection($settings);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};


$container['redis_service'] = static function ($container): RedisService {
    $redis = $container->get('settings')['redis'];

    return new RedisService(new \Predis\Client($redis));
};

# custom errorHandler
$container['errorHandler'] = static fn(): ApiError => new ApiError();


// function ($c) {
//     return function ($request, $response, $exception) use ($c) {
//         if ($exception instanceof \DomainException || $exception instanceof \Firebase\JWT\SignatureInvalidException) {
//             return $response->withJson(['message' => $exception->getMessage()], 401);
//         }

//         if ($exception instanceof \Firebase\JWT\ExpiredException) {
//             return $response->withJson(['message' => 'The provided token as expired.'], 401);
//         }

//         if ($exception instanceof \InvalidArgumentException || $exception instanceof \UnexpectedValueException) {
//             return $response->withJson(['message' => $exception->getMessage()], 400);
//         }

//         $c->logger->critical($exception->getMessage());
//         return $response->withJson(['message' => "Sorry, We're having technical difficulties processing your request. Our Developers would fix this issue as soon as possible."], 500);
//     };
// };
