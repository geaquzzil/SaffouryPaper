<?php

namespace Etq\Restful\Repository;

use Slim\Http\Request;
use Etq\Restful\Middleware\Permissions\BasePermission;
use Etq\Restful\Helpers;
use Exception;
use Slim\Collection;

class Options
{
    // private $staticSearchByColumnRequests = [
    //     "IDS"
    // ];

    public $validateNullValue = true;


    public ?BasePermission $auth = null;

    public Collection $notFoundedColumns;

    public  $addForginsObject;
    public  $addForginsList;
    // private ?array $staticSearchByColumnValues = null;

    private bool $requireParent = true;

    private $recursiveLevel = 1;



    public ?SearchOption $searchOption = null;
    public ?SortOption $sortOption = null;



    public ?string $replaceTableNameInWhereClouser = null;

    public  $listObjects;

    public $forginObjcets;

    public ?Date $date = null;



    public ?int $page = null;
    public ?int  $countPerPage = null;

    public ?int $limit = null;


    private array $staticQuery = [];

    private array $groupBy = [];

    private array $staticSelect = [];

    private array $staticSumSelect = [];

    private array $staticGroupBySelect = [];

    private array $joins = [];

    public array $between = [];






    private ?string $whereHavingQuery = null;

    /// this is for the request if <SizesID>

    public function withGroupByArray($groupBy, ?string $whereHavingQuery = null)
    {
        $this->groupBy = $groupBy;
        $this->whereHavingQuery = $whereHavingQuery;
        return $this;
    }
    public function hasNotFoundedColumn($key)
    {
        return $this->notFoundedColumns->get($key, null);
    }
    public function disableThrowExceptionOnNonFoundColumns()
    {
        $this->throwExceptionOnColumnNotFound = false;
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
    public function getClone(?Options $old = null)
    {
        $instance = clone $this;
        $instance->auth = $old?->auth;
        $instance->tableName = $old?->tableName;
        $instance->replaceTableNameInWhereClouser = $old?->replaceTableNameInWhereClouser;
        return $instance;
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
    public function withJoins(Joins $joins)
    {
        $this->joins[] = $joins;
        return $this;
    }
    public function withStaticSelect($staticQuery)
    {
        $this->staticSelect = $staticQuery;
        return $this;
    }
    public function withStaticSumSelect($staticQuery)
    {
        $this->staticSumSelect = $staticQuery;
        return $this;
    }
    public function withStaticGroupBySelect($staticQuery)
    {
        $this->staticGroupBySelect = $staticQuery;
        return $this;
    }
    public function addGroupBy($groupBy)
    {
        $this->groupBy[] = $groupBy;
        return $this;
    }
    public function addJoin(Joins $field)
    {

        $this->joins[] = $field;
        return $this;
    }
    public function addStaticSelect(?string $field = null)
    {
        if ($field) {
            $this->staticSelect[] = $field;
        }
        return $this;
    }
    public function addStaticSumSelect(?string $field = null)
    {
        if ($field) {
            $this->staticSumSelect[] = $field;
        }
        return $this;
    }
    public function addStaticGroupSelect(?string $field = null)
    {
        if ($field) {
            $this->staticGroupBySelect[] = $field;
        }
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
    public function getSelectQuery($tableName)
    {
        if (empty($this->staticSelect)) {
            return "*";
        } else {

            $arr = $this->staticSelect;
            print_r($this->staticGroupBySelect);
            if (!empty($this->staticGroupBySelect)) {
                $arr = array_merge($arr, $this->staticGroupBySelect);
            }
            if (!empty($this->staticSumSelect)) {
                $arr = array_merge($arr, $this->staticSumSelect);
            }
            print_r($arr);

            return implode(",", $arr);
        }
    }
    public function replaceTableName(string $tableName)
    {
        $this->replaceTableNameInWhereClouser = $tableName;
        return $this;
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
    public static function getInstance(?Options $old = null)
    {
        $instance = new self();
        $instance->auth = $old?->auth;
        $instance->tableName = $old?->tableName;
        $instance->replaceTableNameInWhereClouser = $old?->replaceTableNameInWhereClouser;
        return $instance;
    }

    public function withStaticWhereQuery(?string $query = null)
    {
        $this->addStaticQuery($query);

        return $this;
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
    public function __construct(
        protected ?Request $request = null,
        public $tableName = null,
        public ?SearchRepository $searchRepository = null,
        public bool $throwExceptionOnColumnNotFound = true,

    ) {
        $this->notFoundedColumns = new Collection();

        if (!$request) return;
        $this->auth = $request->getAttribute("Auth", null);
        $bool = is_null(($this->auth)) ? "is auth null" : "not auth null";
        // echo "\n" . $bool . " \n";

        // $this->auths=$request->attrib

        $requestPage = $request->getQueryParam('page', null);
        $requestCountPerPage = $request->getQueryParam('countPerPage', null);
        $requestLimit = $request->getQueryParam('limit', null);
        $searchQuery = $request->getQueryParam('searchQuery', null);
        $searchByField = $request->getQueryParam('searchByField', null);
        $date = $request->getQueryParam('date', null);
        //<>
        $searchByColumn = array();
        $tableName = $searchRepository->changeTableNameToExtended($tableName);
        //#
        // $groupByColumn = array();
        //&
        // $sumByColumn = array();
        foreach (array_keys($request->getQueryParams()) as $ke) {
            $val = $request->getQueryParam($ke, null);
            if (str_starts_with($ke, ">") && str_ends_with($ke, "<") && $val) {
                $key = substr($ke, 1, -1);
                if (Helpers::isJson($val)) {
                    $json =  (Helpers::jsonDecode($val));
                    if ($this->validate($tableName, $key, $json)) {
                        $this->between[$key] = $json;
                    }
                } else {
                    throw new Exception("val should be json from to");
                }
            }
            if (str_starts_with($ke, "<") && str_ends_with($ke, ">") && $val) {
                $key = substr($ke, 1, -1);


                if (Helpers::isJson($val)) {
                    $json =  (Helpers::jsonDecode($val));
                    if (!Helpers::isArray($json)) {
                        throw new Exception("val is not array");
                    } else {
                        // if (array_search($key, $this->staticSearchByColumnRequests)) {
                        // }
                        if ($this->validate($tableName, $key, $json)) {
                            $searchByColumn[$key] = $json;
                        }
                    }
                } else {
                    if ($this->validate($tableName, $key, $val)) {
                        $searchByColumn[$key] = $val;
                    }
                }
            }
            if (str_starts_with($ke, "&") && str_ends_with($ke, "&")) {
                $key = substr($ke, 1, -1);

                if (Helpers::isJson($val)) {
                    throw new Exception("val is not supported");
                    $json =  (Helpers::jsonDecode($val));
                    if (!Helpers::isArray($json)) {
                        throw new Exception("val is not array");
                    } else {
                        // if (array_search($key, $this->staticSearchByColumnRequests)) {
                        // }

                        // $this->withStaticSelect($json);
                    }
                } else {
                    if ($this->validate($tableName, $key, $val)) {
                        $this->addStaticSumSelect($val ? "SUM($key) as $val" : "SUM($key) ");
                    }
                }
            }
            if (str_starts_with($ke, "#") && str_ends_with($ke, "#")) {
                $key = substr($ke, 1, -1);

                if (Helpers::isJson($val)) {
                    throw new Exception("val is not supported");
                    $json =  (Helpers::jsonDecode($val));
                    if (!Helpers::isArray($json)) {
                        throw new Exception("val is not array");
                    } else {
                        // if (array_search($key, $this->staticSearchByColumnRequests)) {
                        // }

                        // $this->withGroupByArray($json);
                    }
                } else {
                    if ($this->validate($tableName, $key, $val)) {
                        $this->addGroupBy($key);
                        $this->addStaticGroupSelect($key);
                    }
                }
            }
        }



        $asc = $request->getQueryParam('ASC', null);
        $desc = $request->getQueryParam('DESC', null);

        $this->page = Helpers::isIntReturnValue($requestPage);
        $this->countPerPage = Helpers::isIntReturnValue($requestCountPerPage);
        $this->limit = Helpers::isIntReturnValue($requestLimit);

        if ($date) {
            $this->date = Date::fromJson(json_decode($date, true));
        }

        if ($searchQuery || $searchByColumn || $searchByField || !empty($this->between)) {
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
    }


    public function validate($tableName = null, $key = null, $value = null)
    {
        if (!$tableName) return;
        $tableColumns = $this->searchRepository->getCachedTableColumns($tableName);
        $tableColumns = array_values($tableColumns);

        $res = Helpers::searchInArray($key, $tableColumns);
        if (!$res) {
            $this->notFoundedColumns->set($key, $value);
            if ($this?->throwExceptionOnColumnNotFound ?? true) {
                throw new \Exception("$key  not Found in column");
            }
        }
        return $res;
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
        $sortQuery = $this->sortOption?->getQuery($this->replaceTableNameInWhereClouser ?? $this->tableName);
        $dateQuery = $this->date?->getQuery($this->replaceTableNameInWhereClouser ?? $tableName);
        $searchQuery = $this->searchOption?->getQuery($tableName, $this->searchRepository, $replaceTableNameInWhereClouser, $this->request, $this);
        $statics = null;
        $groupBy = null;
        $joins = null;
        if (!empty($this->joins)) {
            $joinArr = [];
            foreach ($this->joins as $j) {
                $joinArr[] = $j->getQuery();
            }
            $joins = implode(" ", $joinArr);
        }
        if (!empty($this->staticQuery)) {
            //TODO STATIC QUERY IMPOLDE SHOULD BE AND
            $statics = "WHERE " . implode(" AND ", $this->staticQuery);
        }
        if (!empty($this->groupBy)) {
            $having = $this->whereHavingQuery ? "HAVING " . $this->whereHavingQuery : "";
            $groupBy = "GROUP BY " . implode(",", $this->groupBy)  . "  " . $having;
        }

        if (!$limitQuery && !$sortQuery && !$dateQuery && !$searchQuery && !$statics && !$groupBy && !$joins) {
            return "";
        }
        $whereQuery = "";
        if ($joins) {
            $whereQuery = $whereQuery . $joins;
        }
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
    public function getQuery(?string $tableName = null): string
    {
        if ($tableName) {
            $this->fields = array_map(function ($item) use ($tableName) {
                return " $tableName.$item ";
            }, $this->fields);
        }
        switch ($this->sortType) {
            case SortType::ASC:
                return  " ORDER BY " .  implode(",", $this->fields) . " ASC ";

            case SortType::DESC:
                return  " ORDER BY " .  implode(",", $this->fields) . " DESC ";
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

    public function getQuery(string $tableName, SearchRepository $repo, ?string $replaceTableNameInWhereClouser = null, ?Request $request = null, ?Options $option = null): string
    {
        $searchWhere = array();

        if ($this->searchQuery) {
            $starttime = microtime(true);
            $generatedSearchQuery = $repo->getSearchObjectStringValue($this->searchQuery, $tableName);
            $endtime = microtime(true);
            $duration = $endtime - $starttime;
            echo  "\SearchOption-->->->->-->->->->---->-> $tableName-->->->->-->->->->---->-> \n$generatedSearchQuery\n$duration \n ";
            $searchWhere[] = $generatedSearchQuery;
        }
        if ($option->between) {
            $starttime = microtime(true);
            $generatedSearchQuery = $repo->getSearchQueryBetween($option->between, $tableName);
            $endtime = microtime(true);
            $duration = $endtime - $starttime;
            echo  "\SearchOption-->->->->-->->->->---->-> $tableName-->->->->-->->->->---->-> \n$generatedSearchQuery\n$duration \n ";
            $searchWhere[] = $generatedSearchQuery;
        }
        if ($this->searchByColumn) {
            // print_r($this->searchByColumn);
            $searchWhere[] = $repo->getSearchByColumnQuery($this->searchByColumn, $tableName, $replaceTableNameInWhereClouser, null, $option);
        }
        // print_r($this->searchByColumn);
        // die;

        return implode(" AND ", $searchWhere);
    }
}
enum SortType
{
    case ASC;
    case DESC;
}
enum JoinType
{
    case RIGHT;
    case LEFT;
}
class Joins
{
    public function __construct(public string $tableName, public string $onTableName, public string $onField, public JoinType $joinType, public ?string $onFiledJoined = null) {}
    private function getJoinType()
    {
        switch ($this->joinType) {
            case JoinType::LEFT:
                return "LEFT JOIN";
            case JoinType::RIGHT:
                return "RIGHT JOIN";
            default:
                return "JOIN";
        }
    }
    // RIGHT JOIN `products_search_view` ON `products_search_view`.`iD` =`products`.`iD`
    public function getQuery()
    {
        $join = $this->getJoinType();
        $onFieldJ = $this->onFiledJoined ?? $this->onField;

        return  "$join `$this->tableName` ON `$this->tableName`.`$this->onField` = `$this->onTableName`.`$onFieldJ` ";
    }
}
