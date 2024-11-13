<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends BaseController
{
    public function __invoke(Request $request, Response $response): Response
    {
        $page = $request->getQueryParam('page', null);
        $perPage = $request->getQueryParam('perPage', null);
        $name = $request->getQueryParam('name', null);
        $email = $request->getQueryParam('email', null);

        // $users = $this->getFindUserService()
        //     ->getUsersByPage((int) $page, (int) $perPage, $name, $email);

        return $this->textResponse($response, "GetAll");
    }
}
