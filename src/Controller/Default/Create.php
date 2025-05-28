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
        $input = $this->checkForBody($request);
        $result = $this->container['repository']->add($this->tableName, $input, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
