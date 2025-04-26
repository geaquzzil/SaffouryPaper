<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Etq\Restful\Helpers;
use Etq\Restful\Repository\Date;
use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\Options;
use Etq\Restful\Repository\SearchOption;
use Etq\Restful\Repository\SortOption;
use Etq\Restful\Repository\SearchType;
use Etq\Restful\Repository\SortType;
use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends BaseController
{
    public function __invoke(Request $request, Response $response): Response
    {
        parent::init($request);



        $option = new Options($request);

        // echo "\nquery--->->-->" . $option->getQuery();



        $result = $this->container['repository']->list($this->tableName, null, $option);

        // $users = $this->getFindUserService()
        //     ->getUsersByPage((int) $page, (int) $perPage, $name, $email);
        return $this->jsonResponse($response, 'success', $result, 200);

        // return $this->textResponse($response, "GetAll");
    }
}
