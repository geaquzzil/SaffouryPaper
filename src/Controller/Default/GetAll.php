<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Default;

use Etq\Restful\Controller\BaseController;
use Etq\Restful\Repository\Options;
use Mpdf\Tag\Option;
use Slim\Http\Request;


use Slim\Http\Response;

final class GetAll extends BaseController
{
    public function getOptions(Request $request): Options
    {
        $arr = $this->container->get(AT);
        $key = $this->tableName;
        $throwEx = key_exists($key, $arr) ? false : true;
        return new Options($request, $this->tableName, $this->getSearchRepo(), $throwEx);
    }

    public function __invoke(Request $request, Response $response): Response
    {
        parent::init($request);
        return $this->jsonResponse(
            $response,
            'success',
            $this->container['repository']->list($this->tableName, null, $this->options),
            200
        );
        // return $this->textResponse($response, "GetAll");
    }
}
