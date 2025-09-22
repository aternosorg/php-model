<?php

namespace Aternos\Model\Driver\Mysqli;

use Throwable;

class MysqlConnectionFailedException extends MysqlException
{
    public function __construct(Throwable $previous)
    {
        parent::__construct("Could not connect to Mysqli database", $previous?->getCode(), $previous);
    }
}
