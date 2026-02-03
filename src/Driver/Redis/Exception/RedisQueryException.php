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
        $error = $redis->getLastError();
        if ($error !== false) {
            throw new static("Redis Query Error: " . $error);
        }
    }
}
