<?php

namespace Aternos\Model\Driver\Mysqli;

class RetryCollection
{
    /**
     * @param RetryGroup[] $groups
     */
    public function __construct(protected array $groups = [])
    {
    }

    /**
     * @param RetryGroup $group
     * @return $this
     */
    public function addGroup(RetryGroup $group): static
    {
        $this->groups[] = $group;
        return $this;
    }

    /**
     * @param array $statusCodes
     * @return $this
     */
    public function removeGroup(array $statusCodes): static
    {
        $this->groups = array_filter($this->groups, function (RetryGroup $group) use ($statusCodes) {
            return !$group->matchesStatusCodes($statusCodes);
        });
        return $this;
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    public function canRetry(int $statusCode): bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesStatusCode($statusCode) && $group->hasRetriesLeft()) {
                $group->addRetry();
                return true;
            }
        }
        return false;
    }
}