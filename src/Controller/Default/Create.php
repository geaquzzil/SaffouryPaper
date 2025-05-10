<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

final class Create  extends BaseController
{
    public function __invoke(Request $request, Response $response): Response
    {
        parent::init($request);
        $input = (array) $request->getParsedBody();
        if (!$input) {
            throw new Exception("you dont have any body");
        }
        // $user = $this->getCreateUserService()->create($input);


        $result = $this->container['repository']->add($this->tableName, $input, $this->options);

        // $users = $this->getFindUserService()
        //     ->getUsersByPage((int) $page, (int) $perPage, $name, $email);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
