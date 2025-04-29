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

final class CustomerController extends BaseController
{
    private ?int $iD = null;

    // <!-- ['token/{iD:\d+}', 'Etq\Restful\Controller\CustomerController:createToken', 'post', null],
    //         ['token/{iD:\d+}', 'Etq\Restful\Controller\CustomerController:updateToken', 'put', null],
    //         ['terms[/[{iD:\d+}]]', 'Etq\Restful\Controller\CustomerController:getTearms', 'get', null],
    //         ['profits[/[{iD:\d+}]]', 'Etq\Restful\Controller\CustomerController:getProfits', 'get', null],
    //         ['notPaid[/[{iD:\d+}]]', 'Etq\Restful\Controller\CustomerController:getNotPaidCustomers', 'get', null],
    //         ['overdue[/[{iD:\d+}]]', 'Etq\Restful\Controller\CustomerController:getOverDueCustomers', 'get', null],
    //         ['statement/{iD:\d+}', 'Etq\Restful\Controller\CustomerController:getStatement', 'get', null],
    //         ['balance[/[{iD:\d+}]]', 'Etq\Restful\Controller\CustomerController:getBalance', 'get', null],
    //         ['nextPayment[/[{iD:\d+}]]', 'Etq\Restful\Controller\CustomerController:getNextPayment', 'get', null], -->

    private function initi(Request $request, array $args)
    {
        parent::init($request);
        $val = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $this->iD = $val ? (int)$val : null;
    }

    public function getTerms(Request $request, Response $response, array $args): Response
    {
        // $modelReflector = new \ReflectionClass(__CLASS__);
        // $method = $modelReflector->getMethod(__METHOD__);
        return $this->textResponse($response, "sdds");
    }
    public function getProfits(Request $request, Response $response, array $args): Response
    {
        $modelReflector = new \ReflectionClass(__CLASS__);
        $method = $modelReflector->getMethod(__METHOD__);
        return $this->textResponse($response, $method->name);
    }
    public function getNotPaidCustomers(Request $request, Response $response, array $args): Response
    {
        $modelReflector = new \ReflectionClass(__CLASS__);
        $method = $modelReflector->getMethod(__METHOD__);
        return $this->textResponse($response, $method->name);
    }
    public function getOverDueCustomers(Request $request, Response $response, array $args): Response
    {
        $modelReflector = new \ReflectionClass(__CLASS__);
        $method = $modelReflector->getMethod(__METHOD__);
        return $this->textResponse($response, $method->name);
    }
    public function getStatement(Request $request, Response $response, array $args): Response
    {
        $modelReflector = new \ReflectionClass(__CLASS__);
        // $method = $modelReflector->getMethod(__METHOD__);
        return $this->textResponse($response, '$method->name');
    }
    public function getBalance(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['customer_repository']->getBalance($this->iD, $this->options->date);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getNextPayment(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['customer_repository']->getNextPayment($this->iD, $this->options->date);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getCurrentDayPayment(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);

        $date = $this->options?->date?->unsetFrom();

        $result = $this->container['customer_repository']->getNextPayment($this->iD, $date, true);
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
