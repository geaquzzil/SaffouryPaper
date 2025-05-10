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
        $iD = $this->checkForID($args);
        //TODO BEFORE DELETE 
        //TODO DELETE
        //TODO AFTER DELETE

        $this->getRepository();
        
	$pdo = setupDatabase();
	try {
		$query = "SELECT * FROM  `" . addslashes($tableName) . "` " . getWhereQuery($object);
		$toDeleteObjects = getFetshALLTableWithQuery($query);
		if (empty($toDeleteObjects)) {
			return null;
		}
		$responseArray = array();
		foreach ($toDeleteObjects as $deleteObject) {
			// echo "F";
			$deleteObject["serverStatus"] = doDelete($deleteObject, $tableName, $sendNoti);
			fixDeleteResponseObjectExtenstion($deleteObject, $tableName);
			$responseArray[] = $deleteObject;
			//	array_push($responseArray,$deleteObject);
		}
		return $responseArray;
	} catch (PDOException $e) {
		return null;
	}
        // $userIdLogged = $this->getAndValidateUserId($input);

        // $this->checkUserPermissions($id, $userIdLogged);
        // $this->getDeleteUserService()->delete($id);



        return $this->textResponse($response, "Delete $iD");
    }
}
