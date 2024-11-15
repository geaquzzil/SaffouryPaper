<?php

namespace Etq\Restful\Middleware\Permissions;

use Psr\Http\Message\ResponseInterface;
use Etq\Restful\Helpers;
use Etq\Restful\Repository\PermissionRepository;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Route;
use Firebase\JWT\JWT;
use \Firebase\JWT\Key;

abstract class BasePermission implements ServerActionInterface
{
    private  bool $shouldBeSignedInWhenNoLevelFound = false;
    private $adminID = -1;
    protected PermissionRepository $repo;

    public function __construct(PermissionRepository $repo)
    {
        $this->repo = $repo;
    }

    //$level is array with current user level and default user level
    //if userlevel =0 and action==action is 1 then return false
    //if userlevel=1 and action==action is 1 then return 
    private function checkPermissionTableAccess($levelID, $tableName, $action)
    {

        $level = $this->repo->getPermission($levelID, $tableName);
        // $found_key = array_search('blue', array_column($people, 'fav_color'));
        if (empty($level) || is_null($level)) {
            return $this->shouldBeSignedInWhenNoLevelFound;
        }

        return $level[$action] == 1;
    }
    //If there is  a token it should be valid 
    // if there is no token then guest permssion is applied
    // if  guest has no access then permission denied
    //if non guest has no permssion then  permission denied
    protected function checkForPermission(Request $request, $action)
    {

        $tableName = Helpers::explodeURI($request->getUri()->getPath());
        $token = $this->getToken($request);
        $levelID = 0;
        if (!is_null($token)) {

            $levelID = $token->data->userlevelid;
        }
        if ($levelID == $this->adminID) {

            return;
        }
        $result = $this->checkPermissionTableAccess($levelID, $tableName, $action);
        if (!$result) {
            if ($levelID == 0) {
                throw new \Exception('Token required.', 400);
            } else {
                throw new \Exception('Permission denied.', 400);
            }
        }
    }

    protected function getToken(Request $request)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');

        if (! $jwtHeader) {
            return null;
        }
        $jwt = explode('Bearer ', $jwtHeader);
        if (! isset($jwt[1])) {
            throw new \Exception('Token invalid.', 400);
        }
        $decoded = $this->checkToken($jwt[1]);
        return $decoded;
    }






    protected function checkToken(string $token): object
    {
        try {
            return JWT::decode(
                $token,
                new Key($_SERVER['SECRET_KEY'], 'HS256')

            );
        } catch (\UnexpectedValueException) {
            throw new \Exception('Forbidden: you are not authorized.', 403);
        }
    }
    public function checkIfSignedIn(
        Request $request,
        Response $response,
        Route $next
    ) {

        $jwtHeader = $request->getHeaderLine('Authorization');



        if (! $jwtHeader) {
            throw new \Exception('Token required.', 400);
            return false;
        }
        $jwt = explode('Bearer ', $jwtHeader);
        if (! isset($jwt[1])) {
            throw new \Exception('Token invalid.', 400);
            return false;
        }
        $decoded = $this->checkToken($jwt[1]);
        //todo path uri
        // print_r($request->getUri()->getPath()); 
        $object = (array) $request->getParsedBody();
        $object['decoded'] = $decoded;

        return true;
    }
    private function isCustomer(int $id)
    {
        return $id > 0;
    }
    private function isGuest(int $id)
    {
        return $id = 0;
    }
    private function isEmployee(int $id)
    {
        return $id < 0;
    }

    public function __invoke(
        Request $request,
        Response $response,
        Route $next
    ): ResponseInterface {

        $this->checkForPermission($request, $this->getAction());
        return $next($request, $response);
    }
}


interface ServerActionInterface
{
    public function getAction();
}
