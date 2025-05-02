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

final class ProductController extends BaseController
{
    private ?int $iD = null;
    private function initi(Request $request, array $args)
    {
        parent::init($request);
        $val = Helpers::isSetKeyFromObjReturnValue($args, 'iD');
        $this->iD = $val ? (int)$val : null;
    }
    //TODO bad performance
    public function getMovement(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['product_repository']->getMovement($this->iD, $this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }

    public function getMostPopularProducts(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['product_repository']->getMostPopular($this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getBestSellingProducts(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['product_repository']->getBestSellingProducts($this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
    public function getExpectedProductsToBuy(Request $request, Response $response, array $args): Response
    {
        $this->initi($request, $args);
        $result = $this->container['product_repository']->getExpectedProductsToBuy($this->options);
        return $this->jsonResponse($response, 'success', $result, 200);
    }
}
