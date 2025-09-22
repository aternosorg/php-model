<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Model\Driver\Mysqli;

use Aternos\Model\ModelException;
use mysqli;
use Throwable;

class MysqlException extends ModelException
{
    /**
     * Check if a MySQLi connection has an error and throw an exception if it has
     * @param mysqli $connection
     * @return void
     * @throws MysqlException if the connection has an error
     */
    static function checkConnection(mysqli $connection): void
    {
        if (mysqli_error($connection)) {
            throw static::fromConnection($connection);
        }
    }

    /**
     * Create an exception from a MySQLi connection with an error
     * @param mysqli $connection
     * @return static
     */
    static function fromConnection(mysqli $connection): static
    {
        return new self("MySQLi Error #" . mysqli_errno($connection) . ": " . mysqli_error($connection));
    }

    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
