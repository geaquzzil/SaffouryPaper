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
        // echo $levelID;
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
    /// if current user type == employe then customer and guest is enable
    ///if current user type == customer and 
    private  function hasAccess(int $levelID)
    {
        $currentUserType = $this->checkForUserType($levelID);
        $requiredPriority = $this->getPermissionProiority($this->requiredType->value);
        $currentProiority = $this->getPermissionProiority($levelID);
        if ($this->allowHigherPermission) {
            return $currentProiority >= $requiredPriority;
        } else {
            return $currentUserType == $this->requiredType;
        }
    }
    private function isHigher(UserType $current, UserType $required) {}
}
