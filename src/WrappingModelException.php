<?php

namespace Aternos\Model;

use Throwable;

class WrappingModelException extends ModelException
{
    /**
     * Wrap an existing exception into a ModelException
     * This is used to adapt exceptions from the driver extensions to a ModelException
     * @param Throwable $exception
     * @return static
     */
    static function wrapping(Throwable $exception): static
    {
        return new static($exception->getMessage(), $exception->getCode(), $exception);
    }

    final protected function __construct(string $message, int $code = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
