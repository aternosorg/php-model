<?php

namespace Aternos\Model\Driver\Mysqli;

class RetryGroup
{
    protected int $retries = 0;

    /**
     * @param int[] $statusCodes
     * @param int $maxRetries
     */
    public function __construct(
        protected array $statusCodes = [],
        protected int   $maxRetries = 1,
    )
    {
    }

    /**
     * @return int
     */
    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * @return void
     */
    public function addRetry(): void
    {
        $this->retries++;
    }

    /**
     * @return bool
     */
    public function hasRetriesLeft(): bool
    {
        return $this->retries < $this->maxRetries;
    }

    /**
     * @return int[]
     */
    public function getStatusCodes(): array
    {
        return $this->statusCodes;
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    public function matchesStatusCode(int $statusCode): bool
    {
        return in_array($statusCode, $this->statusCodes);
    }

    /**
     * @param int[] $statusCodes
     * @return bool
     */
    public function matchesStatusCodes(array $statusCodes): bool
    {
        foreach ($statusCodes as $statusCode) {
            if ($this->matchesStatusCode($statusCode)) {
                return true;
            }
        }
        return false;
    }
}