<?php

declare(strict_types=1);

namespace Etq\Restful\Controller;

use Etq\Restful\Controller\BaseController;
use Etq\Restful\Helpers;
use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\Options;
use Etq\Restful\Repository\SearchOption;
use Etq\Restful\Repository\SortOption;
use Etq\Restful\Repository\SearchType;
use Etq\Restful\Repository\SortType;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

final class NotificationController extends BaseController
{
    private function checkForJson($body)
    {
        $data = json_decode((string) json_encode($body), false);
        if (! isset($data->title)) {
            throw new Exception('The field "title" is required.', 400);
        }
        if (! isset($data->body)) {
            throw new Exception('The field "body" is required.', 400);
        }
        return $data;
    }
    private function checkForNotificationService()
    {
        $isDisabled = $this->container['notification_repository']->isNotificationDisabled();
        if ($isDisabled) {
            throw new Exception('notification service is disable contact admin to enable it ', 500);
        }
    }

    public function send(Request $request, Response $response, array $args)
    {
        $this->checkForNotificationService();
        $input = $this->checkForBody($request);
        $this->checkForJson($input);
        parent::init($request);
        $isIDSet = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $isIDSet = $isIDSet ? (int)$isIDSet : null;
        $table = Helpers::isSetKeyFromObjReturnValue($args, 'tableName');
        if ($table) {
            if (strcmp($table, CUST) != 0 && strcmp($table, EMP) != 0) {
                throw new Exception("$table not supported", 400);
            }
        }

        $result = $this->container['notification_repository']->doNotifcationGeneral($input, $table, $isIDSet, null);
        return $this->jsonResponse($response, 'succes', $result, 200);
    }
    public function sendToTopic(Request $request, Response $response, array $args)
    {
        $this->checkForNotificationService();

        $input = $this->checkForBody($request);
        $this->checkForJson($input);
        parent::init($request);
        $topicName = $args["topicName"];
        $result = $this->container['notification_repository']->doNotifcationGeneral($input, null, null, $topicName);
        return $this->jsonResponse($response, 'succes', $result, 200);
    }
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->checkForNotificationService();
        $input = $this->checkForBody($request);
        parent::init($request);
        $isIDSet = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $isDisabled = $this->container['notification_repository']->isNotificationDisabled();
        if ($isDisabled) {
            throw new Exception('notification service is disable contact admin to enable it ');
        }
        if ($isIDSet) {
            echo " is Set $isIDSet";
        } else {
            echo "IS not set";
        }

        return $this->textResponse($response, "Notification");
    }
}
