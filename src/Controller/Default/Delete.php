<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;

final class Delete extends BaseController
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
        $iD = (int)$args['iD'];
        $result = $this->container['repository']->delete($this->tableName, $iD, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
