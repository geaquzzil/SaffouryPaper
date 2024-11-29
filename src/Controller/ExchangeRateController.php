<?php

declare(strict_types=1);

namespace Etq\Restful\Controller;

use DOMDocument;
use DOMXPath;
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

final class ExchangeRateController extends BaseController
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            parent::init($request);
            $path = 'https://sp-today.com/en/currency/us_dollar/city/damascus';
            $content = file_get_contents($path);
            $dom = new DomDocument();
            $dom->loadHTML($content);
            $classname = 'cur-col';
            $finder = new DomXPath($dom);
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
            $tmp_dom = new DOMDocument();
            $buy = '';
            $sell = '';
            $i = 0;
            for ($i = 0; $i < 2; $i++) {
                $val = preg_replace("/[^0-9.]/", "", preg_replace('/\s+/', '', trim($tmp_dom->importNode($nodes[$i], true)->nodeValue)));
                if ($i == 0) {
                    $buy = (int) $val;
                } else {
                    $sell = (int) $val;
                }
            }
            return $this->jsonResponse($response, 'success', [
                "buy" => $buy,
                "sell" => $sell
            ], 200);
            //code...
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
