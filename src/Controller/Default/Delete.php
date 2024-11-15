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
        $input = (array) $request->getParsedBody();
        $iD = $this->checkForID($args);
        // $userIdLogged = $this->getAndValidateUserId($input);

        // $this->checkUserPermissions($id, $userIdLogged);
        // $this->getDeleteUserService()->delete($id);

        return $this->textResponse($response, "Delete $iD");
    }
}
