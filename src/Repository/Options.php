<?php

namespace Etq\Restful\Repository;

use Slim\Http\Request;
use Etq\Restful\Helpers;

class Options
{


    public  $addForginsObject;
    public  $addForginsList;

    private $recursiveLevel = 1;



    public ?SearchOption $searchOption = null;
    public ?SortOption $sortOption = null;

    public  $listObjects;

    public $forginObjcets;

    public ?Date $date = null;



    public ?int $page = null;
    public ?int  $countPerPage = null;

    public ?int $limit = null;


    public function __construct(protected Request $request)
    {
        $requestPage = $request->getQueryParam('page', null);
        $requestCountPerPage = $request->getQueryParam('countPerPage', null);
        $requestLimit = $request->getQueryParam('limit', null);
        $searchQuery = $request->getQueryParam('searchQuery', null);
        $searchByField = $request->getQueryParam('searchByField', null);
        $date = $request->getQueryParam('date', null);


        $asc = $request->getQueryParam('ASC', null);
        $desc = $request->getQueryParam('DESC', null);

        $this->page = Helpers::isIntReturnValue($requestPage);
        $this->countPerPage = Helpers::isIntReturnValue($requestCountPerPage);
        $this->limit = Helpers::isIntReturnValue($requestLimit);

        if ($date) {
            $this->date = Date::fromJson(json_decode($date, true));
        }

        if ($searchQuery) {
            echo " has searchQuery";
            $this->searchOption =  new SearchOption($searchQuery, $searchByField);
            // $option->searchOption =   $searchQuery;
        }
        if ($asc || $desc) {
            echo " has asc || desc";
            if ($asc) {
                $this->sortOption = new SortOption($asc, SortType::ASC);
            } else {
                $this->sortOption = new SortOption($desc, SortType::DESC);
            }
        }

        $this->addForginsObject = $this->checkRequestForginValue($request->getQueryParam("forginObject", null));
        $this->addForginsList =  $this->checkRequestForginValue($request->getQueryParam("forginList", null));
        echo "\nlist -->------>-> " . $this->addForginsList . "  " . $this->isRequestedForginList() . "\n";
        echo "objects -->--->->-> " . $this->addForginsObject . "  " .   $this->isRequestedForginObjects()  . "\n";
    }
    public function isRequestedForginObjects()
    {
        return is_array($this->addForginsObject) || $this->addForginsObject === true;
    }
    public function isRequestedForginList()
    {
        return is_array($this->addForginsList) || $this->addForginsList === true;
    }
    private function checkRequestForginValue($requestAttribute)
    {
        if (is_null($requestAttribute)) return false;
        $isBoolean = Helpers::isBoolean($requestAttribute);
 
        if (Helpers::toBoolean($requestAttribute)) {
            return (bool)$requestAttribute;
        } else if (Helpers::isJson($requestAttribute)) {
            return Helpers::jsonDecode($requestAttribute);
        } else {
            return false;
        }
    }
    public function getQuery(): string
    {
        $limitQuery = $this->getLimitOrPageCountOffset();
        $sortQuery = $this->sortOption?->getQuery();
        $dateQuery = $this->date?->getQuery();
        if (!$limitQuery && !$sortQuery && !$dateQuery) {
            return "";
        }

        $whereQuery =      ($dateQuery) ?  " WHERE $dateQuery" : "";
        //$query = Search//
        $query = $whereQuery . $sortQuery . $limitQuery;

        //TODO 
        //     if(isset($option["CUSTOM_JOIN"])){
        //         $newQuery=$newQuery." ".$option["CUSTOM_JOIN"];
        //     }


        //     if(isset($option["WHERE_EXTENSION"])){
        //         $newQuery=has_word($newQuery,"WHERE")?
        //             ($newQuery." AND ( ".$option["WHERE_EXTENSION"]." )"):
        //             ($newQuery." WHERE ".$option["WHERE_EXTENSION"]);
        //     }
        //     if(isset($option["SEARCH_QUERY"])){

        //     $newQuery=has_word($newQuery,"WHERE")?
        //             ($newQuery." AND ( ".$option["SEARCH_QUERY"]." )"):
        //             ($newQuery." WHERE ".$option["SEARCH_QUERY"]);
        // // 	if(getRequestValue('table')==$tableName){
        // // 	     	if(isset($option["WHERE_EXTENSION"])){
        // // 		$newQuery=$newQuery." ".has_word($newQuery,"WHERE")?
        // // 			($newQuery." AND ( ".$option["WHERE_EXTENSION"]." )"):
        // // 			($newQuery." WHERE ".$option["WHERE_EXTENSION"]);
        // // 	}   
        // // 	    }
        //     }
        //     if(isset($option["ORDER_BY_EXTENSTION"])){
        //         $newQuery=$newQuery.$option["ORDER_BY_EXTENSTION"];
        //     }
        //     if(isset($option["LIMIT"])){
        //         $newQuery=$newQuery." ".$option["LIMIT"];
        //     }





        return $query;
    }
    public function getLimitOrPageCountOffset(): ?string
    {

        if (($this->limit)) {
            return "LIMIT $this->limit";
        }
        if (($this->page) >= 0 && ($this->countPerPage) >= 0) {
            $next_offset = $this->page * $this->countPerPage;
            return "LIMIT $this->countPerPage OFFSET $next_offset";
        }
        return null;
    }
}

class Date
{

    public function __construct(public ?string $from, public ?string $to) {}

    public static function fromJson(array $data): self
    {
        return new self(
            $data['from'] ?? null,
            $data['to'] ?? null
        );
    }

    public function getQuery(): string
    {

        return  "Date(date)  >= '.$this->from.' AND Date(date)<= '.$this->to.'";
    }
}
class SortOption
{
    public function __construct(protected string $field, protected SortType $sortType) {}
    public function getQuery(): string
    {
        switch ($this->sortType) {
            case SortType::ASC;
                return  " ORDER BY `" . $this->field . "` ASC ";

            case SortType::DESC;
                return  " ORDER BY `" . $this->field . "` DESC ";
        }
        return "";
    }
}

class SearchOption
{
    public function __construct(public string $query, public ?string $searchByField = null) {}
    public function getQuery(): string
    {

        if ($SEARCH_QUERY) {

            $option["SEARCH_QUERY"] =  getSearchQueryMasterStringValue(getRequestValue('searchStringQuery'), $tableName);
            if (!empty($RequestTableColumnsCustom)) {

                $whereQuery = array();

                //this line to add AND VIA implode because the implode function does not add any value if array ===1
                foreach ($RequestTableColumnsCustom as $rtc) {
                    $requestValue = getRequestValue("<" . $rtc . ">");
                    $query = getCustomSearchQueryColumnReturnQuery($tableName, $rtc, $requestValue);
                    if (!isEmptyString($query)) {
                        $whereQuery[] =    $query;
                    }
                }
                $joinedQuery = "";
                if (!empty($RequestTableColumns)) {
                    $joinedQuery = ($option["WHERE_EXTENSION"] . " AND " . implode(" AND ", $whereQuery));
                } else {
                    $joinedQuery = implode(" AND ", $whereQuery);
                }

                $option["WHERE_EXTENSION"] =  $joinedQuery;
            }
            if (!empty($RequestTableColumns)) {

                $whereQuery = array();

                //this line to add AND VIA implode because the implode function does not add any value if array ===1
                foreach ($RequestTableColumns as $rtc) {

                    $requestValue = getRequestValue("<" . $rtc . ">");

                    if (!isEmptyString($requestValue)) {
                        $whereQuery[] =    $rtc . " LIKE '" . $requestValue . "'";
                    }
                }

                $joinedQuery = "";
                $joinedQuery = implode(" AND ", $whereQuery);
                //echo $joinedQuery."   sdasda";
                $option["WHERE_EXTENSION"] =  $joinedQuery;
            }
        }



        if ($this->searchByField) {
        } else {
        }
        switch ($this->sortType) {
            case SortType::ASC;
                return  " ORDER BY `" . $this->field . "` ASC ";

            case SortType::DESC;
                return  " ORDER BY `" . $this->field . "` DESC ";
        }
        return "";
    }
}
enum SortType
{
    case ASC;
    case DESC;
}
