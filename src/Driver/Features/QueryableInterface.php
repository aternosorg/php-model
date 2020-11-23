<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;

/**
 * Interface QueryableInterface
 *
 * @package Aternos\Model\Driver\Features
 */
interface QueryableInterface
{
    /**
     * Execute a SELECT, UPDATE or DELETE query
     *
     * @param Query $query
     * @return QueryResult
     */
    public function query(Query $query): QueryResult;
}