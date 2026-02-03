<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Model\Driver\Mysqli;

use Aternos\Model\ModelException;
use mysqli;
use mysqli_sql_exception;
use Throwable;

class MysqliException extends ModelException
{
    /**
     * @param mysqli_sql_exception $exception
     * @return static
     */
    public static function fromException(mysqli_sql_exception $exception): static
    {
        return new static("MySQLi Exception #" . $exception->getCode() . ": " . $exception->getMessage(), $exception->getCode(), $exception);
    }

    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
