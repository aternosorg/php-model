<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;
use Aternos\Model\Query\UpdateQuery;

/**
 * Interface UpdateQueryableInterface
 *
 * @package Aternos\Model\Driver\Features
 */
interface UpdateQueryableInterface extends QueryableInterface
{
    /**
     * @param UpdateQuery|Query $query
     * @return QueryResult
     */
    public function query(Query $query): QueryResult;
}