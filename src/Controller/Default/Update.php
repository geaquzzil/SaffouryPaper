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
        $input = (array) $request->getParsedBody();
        $iD = $this->checkForID($request);
        
        // $userIdLogged = $this->getAndValidateUserId($input);
        // $this->checkUserPermissions($id, $userIdLogged);
        // $user = $this->getUpdateUserService()->update($input, $id);

        return $this->textResponse($response, "Update");
    }
}
