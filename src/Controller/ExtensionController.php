<?php


declare(strict_types=1);

namespace Etq\Restful\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

use Etq\Restful\Repository;


final class ExtensionController extends BaseController
{
    private const API_VERSION = '2.23.0';
    public function getTabels(Request $request, Response $response): Response
    {
        $url = $this->container->get('settings')['app']['domain'];


        $tables = $this->container['repository']->getAllTables();
        $endpoints = array();
        for ($i = 0; $i < count($tables); $i++) {
            $table = $tables[$i];
            $endpoints[$table["table_name"]] = ($url . '/api/v1/' . $table["table_name"]);
        }

        $message = [
            'endpoints' => $endpoints,
            'version' => self::API_VERSION,
            'timestamp' => time(),
        ];

        return $this->jsonResponse($response, 'success', $message, 200);
    }
    public function getHelp(Request $request, Response $response): Response
    {
        $url = $this->container->get('settings')['app']['domain'];
        $db = $this->container->get('db');


        $endpoints = [
            'tasks' => $url . '/api/v1/tasks',
            'users' => $url . '/api/v1/users',
            'notes' => $url . '/api/v1/notes',
            'docs' => $url . '/docs/index.html',
            'status' => $url . '/status',
            'this help' => $url . '',
        ];
        $message = [
            'endpoints' => $endpoints,
            'version' => self::API_VERSION,
            'timestamp' => time(),
        ];

        return $this->jsonResponse($response, 'success', $message, 200);
    }
    public function getPing(Request $request, Response $response): Response
    {
        $data = ['time' => gmdate('Y-m-d H:i:s')];
        return $this->jsonResponse($response, 'success', $data, 200);
    }
    public function getStatus(Request $request, Response $response): Response
    {
        $status = [
            // 'stats' => $this->getDbStats(),
            'MySQL' => 'OK',
            'Redis' => $this->checkRedisConnection(),
            'version' => self::API_VERSION,
            'timestamp' => time(),
        ];

        return $this->jsonResponse($response, 'success', $status, 200);
    }

    /**
     * @return array<int>
     */
    private function getDbStats(): array
    {
        $taskService = $this->container->get('task_service');
        $userService = $this->container->get('find_user_service');
        $noteService = $this->container->get('find_note_service');

        return [
            'tasks' => count($taskService->getAllTasks()),
            'users' => count($userService->getAll()),
            'notes' => count($noteService->getAll()),
        ];
    }

    private function checkRedisConnection(): string
    {
        $redis = 'Disabled';
        if (self::isRedisEnabled() === true) {
            $redisService = $this->container->get('redis_service');
            $key = $redisService->generateKey('test:status');
            $redisService->set($key, new \stdClass());
            $redis = 'OK';
        }

        return $redis;
    }
}