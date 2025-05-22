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


    public function __construct(protected  $requiredType, protected bool $allowHigherPermission = true) {}
    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {
        // echo "\n so  '$this->requiredType'\n";
        // print_r($this->requiredType);
        $this->requiredType = is_int($this->requiredType) ? UserType::tryFrom($this->requiredType) : $this->requiredType;
        // print_r($this->requiredType);
        $token = $this->getToken($request);

        if (!is_null($token)) {
            $this->currentUserID =   $token->data->iD;
            $this->currentID = $token->data->userlevelid;
        }

        if (!$this->hasAccess()) {
            throw new \Exception('Permission denied.', 400);
        }

        $request = $request->withAttribute('Auth', $this);
        

        return $next($request, $response);
    }
    //Not required
    public function getAction()
    {
        return "list";
    }
    /// if current user type == employe then customer and guest is enable
    ///if current user type == customer and 
    private  function hasAccess()
    {
        $levelID = $this->currentID;
        $currentUserType = $this->checkForUserType();
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
