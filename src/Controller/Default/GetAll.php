<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Etq\Restful\Helpers;
use Etq\Restful\Repository\Date;
use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\Options;
use Etq\Restful\Repository\SearchOption;
use Etq\Restful\Repository\SortOption;
use Etq\Restful\Repository\SearchType;
use Etq\Restful\Repository\SortType;
use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends BaseController
{
    public function __invoke(Request $request, Response $response): Response
    {
        parent::init($request);

        $page = $request->getQueryParam('page', null);
        $countPerPage = $request->getQueryParam('countPerPage', null);
        $limit = $request->getQueryParam('limit', null);
        $searchQuery = $request->getQueryParam('searchQuery', null);
        $searchByField = $request->getQueryParam('searchByField', null);
        $date = $request->getQueryParam('date', null);


        $asc = $request->getQueryParam('ASC', null);
        $desc = $request->getQueryParam('DESC', null);


        $option = new Options($request);
        $option->page = Helpers::isIntReturnValue($page);
        $option->countPerPage = Helpers::isIntReturnValue($countPerPage);
        $option->limit = Helpers::isIntReturnValue($limit);


        if ($date) {
            $option->date = Date::fromJson(json_decode($date, true));
        }

        if ($searchQuery) {
            echo " has searchQuery";
            $option->searchOption =  new SearchOption($searchQuery, $searchByField);
            // $option->searchOption =   $searchQuery;
        }
        if ($asc || $desc) {
            echo " has asc || desc";
            if ($asc) {
                $option->sortOption = new SortOption($asc, SortType::ASC);
            } else {
                $option->sortOption = new SortOption($desc, SortType::DESC);
            }
        }
        echo "\n" . $option->getQuery();



        $result = $this->container['repository']->list($this->tableName, $option);

        // $users = $this->getFindUserService()
        //     ->getUsersByPage((int) $page, (int) $perPage, $name, $email);
        return $this->jsonResponse($response, 'success', $result, 200);

        // return $this->textResponse($response, "GetAll");
    }
}
