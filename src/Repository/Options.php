<?php

namespace Etq\Restful\Repository;

use Psr\Http\Message\ServerRequestInterface as Request;

class Options
{
    public ?SearchOption $searchOption = null;
    public ?SortOption $sortOption = null;

    public  $listObjects;

    public $forginObjcets;

    public ?Date $date = null;



    public ?int $page = null;
    public ?int  $countPerPage = null;

    public ?int $limit = null;


    public function __construct(protected Request $request) {}

    public function getQuery(): string
    {
        echo "\nLimitOrPageCountOffset:" . $this->getLimitOrPageCountOffset() ?? "-";
        echo "\nSort:" . $this->sortOption?->getQuery() ?? " - ";
        echo "\nDate:" . $this->date?->getQuery() ?? " - ";
        return ""; //TODO 
    }
    public function getLimitOrPageCountOffset(): ?string
    {

        if (($this->limit)) {
            return "LIMIT $this->limit";
        }
        if (($this->page) && ($this->countPerPage)) {
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
    public function __construct(protected string $query, protected ?SearchType $searchType = null) {}
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
