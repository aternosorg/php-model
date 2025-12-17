<?php

namespace Aternos\Model\Driver\Redis\Exception;

use Throwable;

/**
 * Exception that is thrown when a connection to Redis fails
 */
class RedisConnectionException extends RedisModelException
{
    /**
     * Wrap an existing redis exception into a RedisConnectionException
     * This is used to adapt exceptions from the driver extensions to a ModelException
     * @param Throwable $exception
     * @return static
     */
    static function wrapping(Throwable $exception): static
    {
        return new static($exception->getMessage(), $exception->getCode(), $exception);
    }
}
