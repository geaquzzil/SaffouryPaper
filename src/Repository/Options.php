<?php

namespace Etq\Restful\Repository;

use Slim\Http\Request;
use Etq\Restful\Helpers;

class Options
{


    public  $addForginsObject;
    public  $addForginsList;

    private bool $requireParent = true;

    private $recursiveLevel = 1;



    public ?SearchOption $searchOption = null;
    public ?SortOption $sortOption = null;

    public  $listObjects;

    public $forginObjcets;

    public ?Date $date = null;



    public ?int $page = null;
    public ?int  $countPerPage = null;

    public ?int $limit = null;


    private array $staticQuery = [];

    private array $groupBy = [];
    private array $orderBy = [];

    /// this is for the request if <SizesID>

    public function withGroupByArray($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }
    public function withOrderByArray($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }
    public function addOrderBy($orderBy)
    {
        $this->orderBy[] = $orderBy;
        return $this;
    }
    public function addGroupBy($groupBy)
    {
        $this->groupBy[] = $groupBy;
        return $this;
    }

    public function addStaticQuery(?string $query = null)
    {
        if ($query) {
            $this->staticQuery[] = $query;
        }
        return $this;
    }
    public  function withArray($data)
    {
        foreach ($data as $key => $val) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $val;
            }
        }
    }
    public function requireDetails(?array $arr = null)
    {
        $this->addForginsList = $arr ?? true;
        return $this;
    }
    public function requireObjects(?array $arr = null)
    {
        $this->addForginsObject = $arr ?? true;
        return $this;
    }
    public static function getInstance()
    {
        return new self();
    }
    public static function withStaticWhereQuery($query)
    {
        $instance = new self();
        $instance->addStaticQuery($query);
        return $instance;
    }
    public  function withDate(?Date $date = null)
    {
        if ($date) {
            $this->date = $date;
        }
        return $this;
    }
    public function withASC($field)
    {
        $this->sortOption = new SortOption($field, SortType::ASC);
        return $this;
    }
    public function withDESC($field)
    {
        $this->sortOption = new SortOption($field, SortType::DESC);
        return $this;
    }

    public function __construct(protected ?Request $request = null)
    {
        if (!$request) return;
        $requestPage = $request->getQueryParam('page', null);
        $requestCountPerPage = $request->getQueryParam('countPerPage', null);
        $requestLimit = $request->getQueryParam('limit', null);
        $searchQuery = $request->getQueryParam('searchQuery', null);
        $searchByField = $request->getQueryParam('searchByField', null);
        $date = $request->getQueryParam('date', null);
        $searchByColumn = array();
        foreach (array_keys($request->getQueryParams()) as $ke) {
            $val = $request->getQueryParam($ke, null);
            if (str_starts_with($ke, "<") && str_ends_with($ke, ">") && $val) {
                $searchByColumn[substr($ke, 1, -1)] = $val;
            }
        }
        // print_r($searchByColumn);


        $asc = $request->getQueryParam('ASC', null);
        $desc = $request->getQueryParam('DESC', null);

        $this->page = Helpers::isIntReturnValue($requestPage);
        $this->countPerPage = Helpers::isIntReturnValue($requestCountPerPage);
        $this->limit = Helpers::isIntReturnValue($requestLimit);

        if ($date) {
            $this->date = Date::fromJson(json_decode($date, true));
        }

        if ($searchQuery || $searchByColumn || $searchByField) {
            // echo " has searchQuery";
            $this->searchOption =  new SearchOption($searchQuery, $searchByField, $searchByColumn);
            // $option->searchOption =   $searchQuery;
        }
        if ($asc || $desc) {
            // echo " has asc || desc";
            if ($asc) {
                $this->sortOption = new SortOption($asc, SortType::ASC);
            } else {
                $this->sortOption = new SortOption($desc, SortType::DESC);
            }
        }
        $this->requireParent = true;
        $this->addForginsObject = $this->checkRequestForginValue($request->getQueryParam("forginObject", null));
        $this->addForginsList =  $this->checkRequestForginValue($request->getQueryParam("forginList", null));
        // echo "\nlist -->------>-> " . $this->addForginsList . "  " . $this->isRequestedForginList() . "\n";
        // echo "objects -->--->->-> " . $this->addForginsObject . "  " .   $this->isRequestedForginObjects()  . "\n";
    }



    public function isRequireParent()
    {
        return $this->requireParent;
    }
    public function isRequestedForginObjects()
    {
        return is_array($this->addForginsObject) || $this->addForginsObject == true;
    }
    public function isRequestedForginList()
    {
        return is_array($this->addForginsList) || $this->addForginsList == true;
    }
    private function checkRequestForginValue($requestAttribute)
    {
        if (is_null($requestAttribute)) return false;
        $isBoolean = Helpers::isBoolean($requestAttribute);

        if (!is_null($isBoolean)) {
            return (bool)$isBoolean;
        } else if (Helpers::isJson($requestAttribute)) {
            return Helpers::jsonDecode($requestAttribute);
        } else {
            return false;
        }
    }
    public function getQuery(string $tableName, ?SearchRepository $repo = null): string
    {
        $limitQuery = $this->getLimitOrPageCountOffset();
        $sortQuery = $this->sortOption?->getQuery();
        $dateQuery = $this->date?->getQuery();
        $searchQuery = $this->searchOption?->getQuery($tableName, $repo, $this->request);
        $statics = null;
        $groupBy = null;
        $customOrderBy = null;
        if (!empty($this->staticQuery)) {
            //TODO STATIC QUERY IMPOLDE SHOULD BE AND
            $statics = "WHERE " . implode(" ", $this->staticQuery);
        }
        if (!empty($this->groupBy)) {
            $groupBy = "GROUP BY " . implode(",", $this->groupBy);
        }
        if (!empty($this->orderBy)) {
            $customOrderBy = "ORDER BY " . implode(",", $this->orderBy);
        }
        if (!$limitQuery && !$sortQuery && !$dateQuery && !$searchQuery && !$statics && !$groupBy && !$customOrderBy) {
            return "";
        }
        $whereQuery = "";
        if ($statics) {
            $whereQuery = $whereQuery . $statics;
        }
        if ($dateQuery) {
            $whereQuery = (Helpers::has_word($whereQuery, "WHERE") ?
                ($whereQuery . " AND ( $dateQuery )") : ($whereQuery . " WHERE $dateQuery")) . "\n";
        }
        if ($searchQuery) {
            $whereQuery =  (Helpers::has_word($whereQuery, "WHERE") ?
                ($whereQuery . " AND ( $searchQuery )") : ($whereQuery . " WHERE $searchQuery")) . "\n";
        }
        if ($groupBy) {
            $whereQuery = $whereQuery . "  $groupBy\n";
        }
        if ($customOrderBy) {
            $whereQuery = $whereQuery . "$customOrderBy\n";
        } else {
            if ($sortQuery) {
                $whereQuery = $whereQuery . "  $sortQuery\n";
            }


            if ($limitQuery) {
                $whereQuery = $whereQuery . " $limitQuery\n";
            }
        }
        //TODO 
        //     if(isset($option["CUSTOM_JOIN"])){
        //         $newQuery=$newQuery." ".$option["CUSTOM_JOIN"];
        //     }

        return $whereQuery;
    }
    public function getLimitOrPageCountOffset(): ?string
    {

        if (!is_null(($this->limit))) {

            return "LIMIT $this->limit";
        }

        if (!is_null($this->page)  && !is_null($this->countPerPage)) {
            $next_offset = $this->page * $this->countPerPage;
            return "LIMIT $this->countPerPage OFFSET $next_offset";
        }
        return null;
    }
}

class Date
{

    public static function getInstance()
    {
        return new self(null, null);
    }
    public function getPreviousTo(?string $to = null)
    {
        if ($to) {

            $this->to = $to;
        } else {
            $this->to = date("Y-m-d");
        }
        $this->to = $this->getPreviousDateTo();
        return $this;
    }

    public function unsetFrom()
    {
        $this->from = null;
        return $this;
    }
    public static function to($to)
    {
        return  Date::fromJson([
            "to" => $to
        ]);
    }
    public static function from($from)
    {
        return  Date::fromJson([
            "from" => $from
        ]);
    }
    public static function currentDate()
    {
        return  Date::fromJson([
            "from" => date('Y-m-d'),
            "to" => date('Y-m-d')
        ]);
    }
    public function __construct(public ?string $from, public ?string $to) {}

    public static function fromJson(array $data): self
    {
        return new self(
            $data['from'] ?? null,
            $data['to'] ?? null
        );
    }
    public function getPreviousDateFrom()
    {
        return date('Y-m-d', (strtotime('-1 day', strtotime($this->from))));
    }
    public function getPreviousDateTo()
    {
        return date('Y-m-d', (strtotime('-1 day', strtotime($this->to))));
    }

    public function getQuery(?string $addOptionalTableName = null, string $customDateName = 'date', bool $requireOnlyEquals = false): string
    {
        $dateTableName = "Date($customDateName)";
        if ($addOptionalTableName) {
            $dateTableName
                = "Date($addOptionalTableName.$customDateName)";
        }
        if (!$this->from && !$this->to) {
            return "";
        } else if ($this->from && !$this->to) {
            $from = date("Y-m-d", strtotime($this->from));
            $sign = $requireOnlyEquals ? "=" : ">=";
            return  "( $dateTableName  $sign '$from' )";
        } else if (!$this->from && $this->to) {
            $to = date("Y-m-d", strtotime($this->to));
            $sign = $requireOnlyEquals ? "=" : "<=";
            return  "( $dateTableName  $sign '$to' )";
        } else {
            $from = date("Y-m-d", strtotime($this->from));
            $to = date("Y-m-d", strtotime($this->to));
            $fromSign = $requireOnlyEquals ? "=" : ">=";
            $toSign = $requireOnlyEquals ? "=" : "<=";
            return  "( $dateTableName  $fromSign '$from' AND $dateTableName $toSign '$to') ";
        }
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

    public function __construct(
        public ?string $searchQuery = null,
        public ?string $searchByField = null,
        public array $searchByColumn = []
    ) {}
    public function getQuery(string $tableName, ?SearchRepository $repo = null, ?Request $request): string
    {
        $searchWhere = array();

        if (!is_null($repo) && $this->searchQuery) {
            $starttime = microtime(true);
            $generatedSearchQuery = $repo->getSearchQueryMasterStringValue($this->searchQuery, $tableName);
            $endtime = microtime(true);
            $duration = $endtime - $starttime;
            echo  "\SearchOption-->->->->-->->->->---->-> $tableName-->->->->-->->->->---->-> \n$generatedSearchQuery\n$duration \n ";
            $searchWhere[] = $generatedSearchQuery;
        }
        if ($this->searchByColumn) {
            $searchWhere[] = $repo->getSearchByColumnQuery($this->searchByColumn, $tableName);
        }

        return implode(" AND ", $searchWhere);

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
    }
}
enum SortType
{
    case ASC;
    case DESC;
}
