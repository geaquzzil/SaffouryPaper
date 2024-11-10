<?php

namespace Etq\Restful\Actions;

class Options
{
    private ?SearchOption $searchOption;
    private  $listObjects;

    private $forginObjcets;

    private ?Date $date;

    private ?SortOption $sortOption;

    private ?int $page;
    private ?int  $countPerPage;

    private ?int $limit;


    


    public function getLimitOrPageCountOffset(): ?string
    {
        if (!is_null($this->limit)) {
            return "LIMIT $this->limit";
        }
        if (!is_null($this->page) && !is_null($this->countPerPage)) {
            $next_offset = $this->page * $this->countPerPage;
            return "LIMIT $this->countPerPage OFFSET $next_offset";
        }
        return null;
    }
}

class Date
{
    public function __construct(protected string $from, protected string $to) {}

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
    public function __construct(protected string $query, protected SearchType $searchType) {}
}
enum SearchType
{
    case STRING;
    case FIELD;
}
enum SortType
{
    case ASC;
    case DESC;
}
