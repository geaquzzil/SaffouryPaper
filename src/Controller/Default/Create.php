<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Slim\Http\Request;
use Slim\Http\Response;

final class Create
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = (array) $request->getParsedBody();
        $user = $this->getCreateUserService()->create($input);

        return $this->jsonResponse($response, 'success', $user, 201);
    }
}
