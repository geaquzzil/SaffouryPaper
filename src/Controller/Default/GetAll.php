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
        if ($this->tableName == "products") {
            echo " \nis Table NAme soso\n ";
            return new Options($request, $this->tableName, $this->getSearchRepo(), false);
        }
        return parent::getOptions($request);
    }

    public function __invoke(Request $request, Response $response): Response
    {
        parent::init($request);
        print_r($this->options->notFoundedColumns->all());
        return $this->jsonResponse(
            $response,
            'success',
            $this->container['repository']->list($this->tableName, null, $this->options),
            200
        );
        // return $this->textResponse($response, "GetAll");
    }
}
