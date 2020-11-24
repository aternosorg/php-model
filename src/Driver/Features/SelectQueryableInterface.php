<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;
use Aternos\Model\Query\SelectQuery;

/**
 * Interface SelectQueryableInterface
 *
 * @package Aternos\Model\Driver\Features
 */
interface SelectQueryableInterface extends QueryableInterface
{
    /**
     * @param SelectQuery|Query $query
     * @return QueryResult
     */
    public function query(Query $query): QueryResult;
}