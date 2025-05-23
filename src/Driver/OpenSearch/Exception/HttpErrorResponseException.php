<?php

namespace Aternos\Model\Driver\OpenSearch\Exception;

use stdClass;
use Throwable;

class HttpErrorResponseException extends HttpException
{
    /**
     * @param stdClass|null $responseBody
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        protected ?stdClass $responseBody,
        string              $message = "",
        int                 $code = 0,
        ?Throwable          $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return stdClass|null
     */
    public function getResponseBody(): ?stdClass
    {
        return $this->responseBody;
    }
}
