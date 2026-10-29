<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Model\Test\Tests;

use Aternos\Model\Driver\Redis\Exception\RedisQueryException;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Redis;

#[RequiresPhpExtension('redis')]
class RedisQueryExceptionTest extends TestCase
{
    protected function createRedis(?string $lastError): Redis
    {
        return new class($lastError) extends Redis {
            public bool $cleared = false;

            public function __construct(protected ?string $lastError)
            {
                parent::__construct();
            }

            public function getLastError(): ?string
            {
                return $this->lastError;
            }

            public function clearLastError(): bool
            {
                $this->cleared = true;
                $this->lastError = null;
                return true;
            }
        };
    }

    public function testNoErrorDoesNotThrow(): void
    {
        $redis = $this->createRedis(null);
        RedisQueryException::checkConnection($redis);
        $this->assertFalse($redis->cleared);
    }

    public function testErrorThrows(): void
    {
        $this->expectException(RedisQueryException::class);
        $this->expectExceptionMessage("Redis Query Error: ERR something went wrong");
        RedisQueryException::checkConnection($this->createRedis("ERR something went wrong"));
    }

    public function testErrorIsClearedAfterThrowing(): void
    {
        $redis = $this->createRedis("ERR something went wrong");
        try {
            RedisQueryException::checkConnection($redis);
            $this->fail("Expected RedisQueryException");
        } catch (RedisQueryException) {
        }

        $this->assertTrue($redis->cleared);
        RedisQueryException::checkConnection($redis);
    }
}
