<?php

namespace Aternos\Model\Query;

/**
 * Class QueryResultCollection
 *
 * Contains multiple QueryResults for Update and Delete queries
 *
 * @package Aternos\Model\Query
 */
class QueryResultCollection extends QueryResult
{
    /**
     * @var array|QueryResult[]
     */
    protected array $models = [];

    /**
     * QueryResultCollection constructor.
     * @param bool $success
     * @param array|QueryResult[] $result
     * @param string|null $queryString
     */
    public function __construct(bool $success, $result = [], ?string $queryString = null)
    {
        parent::__construct($success, $result, $queryString);
    }

    /**
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        foreach ($this->models as $result) {
            if (!$result->wasSuccessful()) {
                return false;
            }
        }
        return true;
    }
}