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
use Slim\Http\Request;
use Slim\Http\Response;

final class NotificationController extends BaseController
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        parent::init($request);
        $isIDSet = Helpers::isSetKeyFromObjReturnValue($args, 'iD');

        if ($isIDSet) {
            echo " is Set $isIDSet";
        } else {
            echo "IS not set";
        }

        //action_notification


        // // echo $this->tableName;
        // $page = $request->getQueryParam('page', null);
        // $countPerPage = $request->getQueryParam('countPerPage', null);
        // $limit = $request->getQueryParam('limit', null);
        // $searchQuery = $request->getQueryParam('searchQuery', null);


        // $asc = $request->getQueryParam('ASC', null);
        // $desc = $request->getQueryParam('DESC', null);


        // $option = new Options($request);
        // $option->page = Helpers::isIntReturnValue($page);
        // $option->countPerPage = Helpers::isIntReturnValue($countPerPage);
        // $option->limit = Helpers::isIntReturnValue($limit);

        // if ($searchQuery) {
        //     echo " has searchQuery";
        //     $option->searchOption =  new SearchOption($searchQuery);
        //     // $option->searchOption =   $searchQuery;
        // }
        // if ($asc || $desc) {
        //     echo " has asc || desc";
        //     if ($asc) {
        //         $option->sortOption = new SortOption($asc, SortType::ASC);
        //     } else {
        //         $option->sortOption = new SortOption($desc, SortType::DESC);
        //     }
        // }
        // echo "\n" . $option->getQuery();



        // // $this->container['repository']->;

        // // $users = $this->getFindUserService()
        // //     ->getUsersByPage((int) $page, (int) $perPage, $name, $email);

        return $this->textResponse($response, "Notification");
    }
}
