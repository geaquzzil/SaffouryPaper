<?php


namespace Etq\Restful\Middleware;

use Etq\Restful\Middleware\Permissions\BasePermission;
use Etq\Restful\Middleware\Permissions\UserType;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;

class Auth extends BasePermission
{
    private $adminID = -1;


    public function __construct(protected UserType $requiredType, protected bool $allowHigherPermission = true) {}
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {

        $token = $this->getToken($request);
        $levelID = 0;
        if (!is_null($token)) {
            $levelID = $token->data->userlevelid;
        }
        if (!$this->hasAccess($levelID)) {
            throw new \Exception('Permission denied.', 400);
        }


        return $next($request, $response);
    }
    //Not required
    public function getAction()
    {
        return "list";
    }

    private  function hasAccess(int $levelID)
    {
        if ($this->allowHigherPermission) {

            if()
            if ($this->requiredType == UserType::EMPLOYEE) {
                return $levelID < -1 ;
            } else if ($this->requiredType == UserType::ADMIN) {
                return $levelID == -1;
            } else if ($this->requiredType == UserType::GUEST) {
                return $levelID == 0;
            } else {
                return
            }
        } else {
            return $this->checkForUserType($levelID) == $this->requiredType;
        }
    }
    private function isHigher(UserType $current, UserType $required) {}
}
