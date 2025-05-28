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
    private function initi(Request $request, array $args)
    {
        parent::init($request);
        $val = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $this->iD = $val ? (int)$val : null;
    }

    public function transfer(Request $request, Response $response, array $args)
    {

        $this->initi($request, $args);
        $from = (int)Helpers::isSetKeyFromObjReturnValue($args, "from");
        $to = (int)Helpers::isSetKeyFromObjReturnValue($args, "to");
        $result = $this->container['customer_repository']->transfer($from, $to, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    //TODO bad performance
    public function getProfits(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['customer_repository']->getProfits($this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }

    public function getStatement(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['customer_repository']->getStatement($this->iD, $this->options->date, true);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getBalance(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['customer_repository']->getBalance($this->iD, $this->options->date);
        return $this->jsonResponse($response, 'success', $result, 200);
    }


    public function getOverDueReservationInvoice(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);

        $date = $this->options?->date;

        $result = $this->container['customer_repository']->getOverDueReservationInvoice($this->iD, $date, false, true);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getOverDueCustomers(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);

        $date = $this->options?->date;

        $result = $this->container['customer_repository']->getNextAndOverDuePayment($this->iD, $date, false, true);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getNextPayment(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['customer_repository']->getNextAndOverDuePayment($this->iD, $this->options->date);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getCurrentDayPayment(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);

        $date = $this->options?->date?->unsetFrom();

        $result = $this->container['customer_repository']->getNextAndOverDuePayment($this->iD, $date, true);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function updateToken(Request $request, Response $response)
    {
        $this->initi($request, []);
        $input = $this->checkForBody($request);
        if (!Helpers::isSetKeyFromObj($input, "token")) {
            throw new \Exception("you dont have token");
        }

        return $this->jsonResponse(
            $response,
            'success',
            $this->container
                ->get("user_repository")
                ->updateToken(Helpers::getKeyValueFromObj($input, "token"), $this->options),
            200
        );
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
