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
     * @param array|QueryResult[] $models
     * @param string|null $queryString
     */
    public function __construct($models = [], ?string $queryString = null)
    {
        parent::__construct($models, $queryString);
    }
}
