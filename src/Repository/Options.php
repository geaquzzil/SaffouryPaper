<?php

namespace Etq\Restful\Repository;

use Slim\Http\Request;
use Etq\Restful\Helpers;
use Exception;

class Options
{
    // private $staticSearchByColumnRequests = [
    //     "IDS"
    // ];

    public  $addForginsObject;
    public  $addForginsList;
    // private ?array $staticSearchByColumnValues = null;

    private bool $requireParent = true;

    private $recursiveLevel = 1;


    public ?SearchRepository $searchRepository;
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

    private ?string $whereHavingQuery = null;

    /// this is for the request if <SizesID>

    public function withGroupByArray($groupBy, ?string $whereHavingQuery = null)
    {
        $this->groupBy = $groupBy;
        $this->whereHavingQuery = $whereHavingQuery;
        return $this;
    }

    public function withLimit(?int $limit = null)
    {
        if ($limit) {
            $this->limit = $limit;
        }
        return $this;
    }
    public function unsetDate()
    {
        $this->date = null;
        return $this;
    }
    public function getClone()
    {
        return clone $this;
    }
    public function removeDate()
    {
        $this->date = null;
        return $this;
    }
    public function addOrderBy(?string $orderBy = null)
    {
        if ($orderBy) {
            if (!$this->sortOption) {
                $this->sortOption = new SortOption([$orderBy], SortType::ASC);
            } else {
                $this->sortOption->addField($orderBy);
            }
        }
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
    public static function withStaticWhereQuery(?string $query = null)
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
    public function withASCArray(?array $field = null)
    {
        $this->sortOption = $field ? new SortOption($field, SortType::ASC) : null;
        return $this;
    }
    public function withDESCArray(?array $field = null)
    {
        $this->sortOption = $field ? new SortOption($field, SortType::DESC) : null;
        return $this;
    }
    public function getRequestColumnValue($key)
    {
        if ($this->isSetRequestColumnsKey($key)) {
            return $this->searchOption?->searchByColumn[$key];
        }
        return array();
    }
    public function setOrChangeRequestColumnValue($key, $val)
    {
        if (!$this->searchOption) {
            $arr = array();
            $arr[$key] = $val;
            $this->searchOption = new SearchOption(null, null, $arr);
            return $this;
        }
        $this->searchOption->searchByColumn[$key] = $val;
        return $this;
    }
    public function isSetRequestColumnsKey($key): bool
    {
        $arr = $this->searchOption?->searchByColumn;
        if (!$arr) return false;
        if (empty($arr)) {
            return false;
        }
        return key_exists($key, $arr);
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
                $key = substr($ke, 1, -1);

                if (Helpers::isJson($val)) {
                    $json =  (Helpers::jsonDecode($val));
                    if (!Helpers::isArray($json)) {
                        throw new Exception("val is not array");
                    } else {
                        // if (array_search($key, $this->staticSearchByColumnRequests)) {
                        // }
                        $searchByColumn[$key] = $json;
                    }
                } else {
                    $searchByColumn[$key] = $val;
                }
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
                $this->sortOption = new SortOption([$asc], SortType::ASC);
            } else {
                $this->sortOption = new SortOption([$desc], SortType::DESC);
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
    public function getQuery(string $tableName, ?string $replaceTableNameInWhereClouser = null): string
    {
        $limitQuery = $this->getLimitOrPageCountOffset();
        $sortQuery = $this->sortOption?->getQuery();
        $dateQuery = $this->date?->getQuery($tableName);
        $searchQuery = $this->searchOption?->getQuery($tableName, $this->searchRepository, $replaceTableNameInWhereClouser, $this->request);
        $statics = null;
        $groupBy = null;

        if (!empty($this->staticQuery)) {
            //TODO STATIC QUERY IMPOLDE SHOULD BE AND
            $statics = "WHERE " . implode(" AND ", $this->staticQuery);
        }
        if (!empty($this->groupBy)) {
            $having = $this->whereHavingQuery ? "HAVING " . $this->whereHavingQuery : "";
            $groupBy = "GROUP BY " . implode(",", $this->groupBy)  . "  " . $having;
        }

        if (!$limitQuery && !$sortQuery && !$dateQuery && !$searchQuery && !$statics && !$groupBy) {
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
        if ($sortQuery) {
            $whereQuery = $whereQuery . "  $sortQuery\n";
        }


        if ($limitQuery) {
            $whereQuery = $whereQuery . " $limitQuery\n";
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
    public function getMonthNumber(bool $from)
    {
        return date('m',  strtotime($from ? $this->from : $this->to));
    }
    public function getPreviousTo(?string $to = null)
    {
        $instance = clone $this;

        if ($to) {
            $instance->to = $to;
        } else {
            $instance->to = date("Y-m-d");
        }
        $instance->to = $instance->getPreviousDateTo();
        return $instance->unsetFrom();
    }

    public function unsetFrom()
    {
        $instance = clone $this;
        $instance->from = null;
        return $instance;
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
    public function addField($field)
    {
        $this->fields[] = $field;
    }
    public function __construct(public array $fields, protected SortType $sortType) {}
    public function getQuery(): string
    {
        switch ($this->sortType) {
            case SortType::ASC:
                return  " ORDER BY `" .  implode(",", $this->fields) . "` ASC ";

            case SortType::DESC:
                return  " ORDER BY `" .  implode(",", $this->fields) . "` DESC ";
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
    public function getQuery(string $tableName, SearchRepository $repo, ?string $replaceTableNameInWhereClouser = null, ?Request $request): string
    {
        $searchWhere = array();

        if ($this->searchQuery) {
            $starttime = microtime(true);
            $generatedSearchQuery = $repo->getSearchQueryMasterStringValue($this->searchQuery, $tableName);
            $endtime = microtime(true);
            $duration = $endtime - $starttime;
            echo  "\SearchOption-->->->->-->->->->---->-> $tableName-->->->->-->->->->---->-> \n$generatedSearchQuery\n$duration \n ";
            $searchWhere[] = $generatedSearchQuery;
        }
        if ($this->searchByColumn) {
            $searchWhere[] = $repo->getSearchByColumnQuery($this->searchByColumn, $tableName, $replaceTableNameInWhereClouser);
        }
        // print_r($this->searchByColumn);
        // die;

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
