<?php

declare(strict_types=1);

namespace Etq\Restful\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiError extends \Slim\Handlers\Error
{
    public function __invoke(
        Request $request,
        Response $response,
        \Exception $exception
    ): Response {
        $statusCode = $this->getStatusCode($exception);
        $className = new \ReflectionClass($exception::class);
        $data = [
            'message' => $exception->getMessage(),
            'class' => $className->getName(),
            'trace' => $exception->getTraceAsString(),
            // 'trace' => $this->getExceptionTraceAsString($exception),
            'status' => 'error',
            'code' => $statusCode,
        ];
        $body = json_encode($data,  JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write((string) $body);
        print_r($data);
        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-type', 'application/problem+json');
    }
    private function getExceptionTraceAsString($exception)
    {
        $rtn = "";
        $count = 0;
        foreach ($exception->getTrace() as $frame) {
            $args = "";
            if (isset($frame['args'])) {
                $args = array();
                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }
                $args = join(", ", $args);
            }
            $rtn .= sprintf(
                "#%s %s\n(%s): %s%s%s(%s)\n",
                $count,
                $frame['file'],
                $frame['line'],
                isset($frame['class']) ? $frame['class'] : '',
                isset($frame['type']) ? $frame['type'] : '', // "->" or "::"
                $frame['function'],
                $args
            );
            $count++;
        }
        return $rtn;
    }
    private function getStatusCode(\Exception $exception): int
    {
        $statusCode = 500;
        if (
            is_int($exception->getCode()) &&
            $exception->getCode() >= 400 &&
            $exception->getCode() <= 500
        ) {
            $statusCode = $exception->getCode();
        }

        return $statusCode;
    }
}
