<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;

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
        // $user = $this->getFindUserService()->getOne((int) $args['id']);

        return $this->textResponse($response, "GetOne");
    }
}
