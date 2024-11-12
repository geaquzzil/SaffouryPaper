<?php

namespace Etq\Restful\Extensions;

class Actions extends Base {

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
}
