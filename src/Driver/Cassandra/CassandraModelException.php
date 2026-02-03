<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Model\Driver\Cassandra;

use Aternos\Model\ModelException;
use Throwable;

class CassandraModelException extends ModelException
{
    /**
     * Wrap an existing exception into a CassandraModelException
     * This is used to adapt exceptions from the cassandra extension to a ModelException
     * @param Throwable $exception
     * @return static
     */
    static function wrapping(Throwable $exception): static
    {
        return new self($exception->getMessage(), $exception->getCode(), $exception);
    }
}
