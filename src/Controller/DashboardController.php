<?php

declare(strict_types=1);

namespace Etq\Restful\Controller;

use Etq\Restful\Controller\BaseController;
use Etq\Restful\Helpers;
use Etq\Restful\Repository\Repository;
use Etq\Restful\Middleware\Auth;
use Etq\Restful\Repository\Options;
use Etq\Restful\Repository\SearchOption;
use Etq\Restful\Repository\SortOption;
use Etq\Restful\Repository\SearchType;
use Etq\Restful\Repository\SortType;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

final class DashboardController extends BaseController
{
    private ?int $iD = null;
    public function getOptions(Request $request): Options
    {
        return new Options($request, $this->tableName, $this->getSearchRepo(), false);
    }

    private function initi(Request $request, array $args)
    {
        parent::init($request);
        $val = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $this->iD = $val ? (int)$val : null;
    }

    public function getDashboard(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);

        // $date = $this->options?->date?->unsetFrom();

        $auth =   $request->getAttribute('Auth');
        // $val = $auth->isEmployee() ? "True" : "false";
        // $iD = $auth->getUserID();
        $result = $this->container['dashboard_repository']->getDashboard($this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getMoneyDashboard(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);

        // $date = $this->options?->date?->unsetFrom();

        $auth =   $request->getAttribute('Auth');
        // $val = $auth->isEmployee() ? "True" : "false";
        // $iD = $auth->getUserID();
        $result = $this->container['dashboard_repository']->getMoneyDashboard($this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }

    //equalivnt of list_dashboard_single_item
    public function dashIT(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['dashboard_repository']->dashIT($this->tableName, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getNotUsedRecords(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['dashboard_repository']->getNotUsedRecords($this->tableName, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }

    public function deleteNotUsedRecords(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['dashboard_repository']->deleteNotUsedRecords($this->tableName, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getChangedRecords(Request $request, Response $response) {}


    // public function getTearms(Request $request, Response $response, array $args): Response
    // {
    //     $modelReflector = new \ReflectionClass(__CLASS__);
    //     $method = $modelReflector->getMethod(__METHOD__);
    //     return $this->textResponse($response, $method->name);
    // }

    // public function getProfits(Request $request, Response $response, array $args): Response
    // {
    //     $modelReflector = new \ReflectionClass(__CLASS__);
    //     $method = $modelReflector->getMethod(__METHOD__);
    //     return $this->textResponse($response, $method->name);
    // }
}
