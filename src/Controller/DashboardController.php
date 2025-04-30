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
    private function initi(Request $request, array $args)
    {
        parent::init($request);
        $val = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $this->iD = $val ? (int)$val : null;
    }
    public function getDashboard(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);

        $date = $this->options?->date?->unsetFrom();

        $auth =   $request->getAttribute('Auth');
        $val = $auth->isEmployee() ? "True" : "false";
        $iD = $auth->getUserID();
        $result = $this->container['dashboard_repository']->getDashboard($auth, $this->options->date);
        return $this->jsonResponse($response, 'success', $result, 200);
    }

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
