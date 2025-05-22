<?php

declare(strict_types=1);

namespace Etq\Restful\Controller;

use Etq\Restful\Controller\BaseController;
use Etq\Restful\Helpers;
use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\Options;
use Etq\Restful\Repository\SearchOption;
use Etq\Restful\Repository\SortOption;
use Etq\Restful\Repository\SearchType;
use Etq\Restful\Repository\SortType;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

final class BlockController extends BaseController
{


    public function __invoke(Request $request, Response $response, array $args): Response
    {
        parent::init($request);
        $isIDSet = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $isIDSet = $isIDSet ? (int)$isIDSet : null;
        $table = Helpers::isSetKeyFromObjReturnValue($args, 'tableName');
        if ($table) {
            if (strcmp($table, CUST) != 0 && strcmp($table, EMP) != 0) {
                throw new Exception("$table not supported");
            }
        }
        $result = $this->container['user_repository']->block($table, true, $isIDSet, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }

    public function unblock(Request $request, Response $response, array $args)
    {
        parent::init($request);

        $isIDSet = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $isIDSet = $isIDSet ? (int)$isIDSet : null;
        $table = (string)Helpers::isSetKeyFromObjReturnValue($args, 'tableName');
        $int = strcmp($table, CUST);
        if ($table) {
            if (strcmp($table, CUST) != 0 && strcmp($table, EMP) != 0) {
                throw new Exception("$table not supported");
            }
        }
        $result = $this->container['user_repository']->block($table, false, $isIDSet, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
