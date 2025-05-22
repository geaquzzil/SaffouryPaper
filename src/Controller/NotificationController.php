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
    public function __invoke(Request $request, Response $response, array $args): Response
    {
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
