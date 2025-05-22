<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;

final class Update extends BaseController
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
        $input = (array) $request->getParsedBody();
        if (!$input) {
            throw new \Exception("you dont have any body");
        }
        $iD = (int)$args['iD'];


        $result = $this->container['repository']->edit($this->tableName, $iD, $input, $this->options);

        // $users = $this->getFindUserService()
        //     ->getUsersByPage((int) $page, (int) $perPage, $name, $email);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
