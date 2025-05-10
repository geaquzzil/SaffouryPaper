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
        $iD = (int)$args['iD'];
        $result = $this->container['repository']->view($this->tableName, $iD, null, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
