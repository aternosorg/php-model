<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Model\Driver\Redis\Exception;

use Redis;

/**
 * Exception that is thrown when a Redis query fails
 */
class RedisQueryException extends RedisModelException
{
    /**
     * @param Redis $redis
     * @return void
     * @throws RedisQueryException if the Redis instance has an error
     */
    public static function checkConnection(Redis $redis): void
    {
        // getLastError() returns null if there is no error (or false if there is no connection)
        $error = $redis->getLastError();
        if (is_string($error)) {
            // the error is kept until it's cleared, so it would be reported again for the next query
            $redis->clearLastError();
            throw new static("Redis Query Error: " . $error);
        }
    }
}
