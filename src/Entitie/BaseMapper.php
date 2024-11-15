<?php

namespace Etq\Restful\Actions;

use Etq\Restful\Actions\Options;
use Etq\Restful\Actions\ServerAction;

abstract class BaseMapper
{


    public function __construct(protected \PDO $db) {}


    private function getQuery(string $tableName, ServerAction $action, ?Options $option = null): string
    {
        switch ($action) {
            case ServerAction::ADD:

            case ServerAction::EDIT:

            case ServerAction::LIST:

            case ServerAction::DELETE:

            case ServerAction::VIEW:
        }
        return "";
    }
    private function getOption(?Options $option): string
    {
        if (!$option) return "";

        return $option->getQuery();
    }
}
