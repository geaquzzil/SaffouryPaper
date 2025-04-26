<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use Etq\Restful\Repository\Options;
use Etq\Restful\Helpers;

final class GetOne extends BaseController
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        parent::init($request);

        // $user = $this->getFindUserService()->getOne((int) $args['id']);
        $option = new Options($request);

        // echo "\nquery--->->-->" . $option->getQuery();

        $iD  = (int)Helpers::explodeURIGetID($request->getUri()->getPath());

        $result = $this->container['repository']->view($this->tableName, $iD, null, $option);

        // $users = $this->getFindUserService()
        //     ->getUsersByPage((int) $page, (int) $perPage, $name, $email);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
