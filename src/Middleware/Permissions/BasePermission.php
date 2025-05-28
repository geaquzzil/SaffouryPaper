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
use Psr\Http\Message\StreamInterface;

abstract class BasePermission implements ServerActionInterface
{
    public static
        $permissionExtentions = array(
            "action_exchange_rate",
            "action_block",
            "action_transfer_account",
            "action_notification",
            "action_change_customers_password",
            "action_cut_request_scan_by_product",
            "action_cut_request_change_quantity",
            "text_products_quantity",
            // "text_customers_password",
            "text_customers_balances",
            "text_transaction_added_by",
            "text_prices_for_customer",
            "text_products_notes",
            "text_purchase_price",
            "text_balance_due",
            "text_balance_due_today",
            "text_balance_due_previous",
            //"list_customers_terms",
            "add_from_importer",

            "set_customs_declarations",

            "list_product_movement",
            "list_customers_balances",
            "list_block",
            "list_fund",
            "list_dashboard",
            "list_sales",
            "list_profit_loses",
            "list_products_movements",
            "view_customer_statment_by_employee"



        );
    private  bool $shouldBeSignedInWhenNoLevelFound = false;
    private $adminID = -2;
    protected $currentID = 0;
    protected $currentUserID = -1;


    public function __construct(protected PermissionRepository $repo) {}
    public function  getUserID()
    {
        if ($this->currentUserID == -1) {
            return null;
        }
        return $this->currentUserID;
    }
    //$level is array with current user level and default user level
    //if userlevel =0 and action==action is 1 then return false
    //if userlevel=1 and action==action is 1 then return 
    private function checkPermissionTableAccess($levelID, $tableName, $action)
    {

        $level = $this->repo->getPermission($levelID, $tableName);
        // print_r($level);
        // $found_key = array_search('blue', array_column($people, 'fav_color'));
        if (empty($level) || is_null($level)) {
            return $this->shouldBeSignedInWhenNoLevelFound;
        }

        return $level[$action] == 1;
    }
    protected function getTableName($request)
    {
        return Helpers::explodeURIGetTableName($request->getUri()->getPath());
    }

    public function checkForPermissionBoolean($tableName)
    {
        return  $this->checkPermissionTableAccess($this->currentID, $tableName, $this->getAction());
    }

    //If there is  a token it should be valid 
    // if there is no token then guest permssion is applied
    // if  guest has no access then permission denied
    //if non guest has no permssion then  permission denied
    protected function checkForPermission(Request $request, $action)
    {

        $tableName = $this->getTableName($request);
        $token = $this->getToken($request);
        if (!is_null($token)) {

            $this->currentUserID =   $token->data->iD;
            $this->currentID  = $token->data->userlevelid;
        }
        if ($this->currentID  == $this->adminID) {

            return;
        }
        $result = $this->checkPermissionTableAccess($this->currentID, $tableName, $action);
        if (!$result) {
            if ($this->currentID == 0) {
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


    protected function checkToSendNotification(Request $request, Response $response)
    {
        //todo
        $tableName = $this->getTableName($request);
        $action = $this->getAction();
        echo "  \nim in checkToSend Notificaiton\n :tableNAme $tableName, Action is : $action \n";

        $originalObject = (array)$request->getParsedBody();
        // StreamInterface $s; 
        $responseObjcet = Helpers::jsonDecode((string)$response->getBody());

        // print_r($originalObject);
        // print_r($responseObjcet);
        //check for if notification system is enable

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
    public function isCustomer(?int $id = null)
    {
        $id = $id ?? $this->currentID;
        return $id > 0;
    }
    public function isGuest(?int $id = null)
    {
        $id = $id ?? $this->currentID;
        return $id = 0;
    }
    public function isEmployee(?int $id = null)
    {
        $id = $id ?? $this->currentID;
        return $id < 0;
    }
    public function isAdmin(?int $id = null)
    {
        $id = $id ?? $this->currentID;
        return $id == $this->adminID;
    }
    protected  function checkForUserType()
    {
        $levelID = $this->currentID;
        if ($levelID == 0) {
            return UserType::GUEST;
        } else if ($levelID == $this->adminID) {
            return UserType::ADMIN;
        } else if ($levelID > 0) {
            return UserType::CUSTOMER;
        } else {
            return UserType::EMPLOYEE;
        }
    }
    protected function getPermissionProiority(int $id)
    {
        if ($id == $this->adminID) {
            return 3;
        } else if ($id == 0) {
            return 0;
        } else if ($id > 0) {
            return 1;
        } else {
            return 2;
        }
    }

    public function invoke(
        Request $request,
        Response $response,
        Route $next
    ): void {

        $this->checkForPermission($request, $this->getAction());
    }
    protected function init() {}
}


interface ServerActionInterface
{
    public function getAction();
}
enum UserType: int
{
    case  GUEST = 0;
    case EMPLOYEE = -4;
    case CUSTOMER = 1;
    case ADMIN = -1;
}
