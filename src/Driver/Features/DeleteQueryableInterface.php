<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\Query\DeleteQuery;
use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;

/**
 * Interface DeleteQueryableInterface
 * @package Aternos\Model\Driver\Features
 */
interface DeleteQueryableInterface extends QueryableInterface
{
    /**
     * @param DeleteQuery|Query $query
     * @return QueryResult
     */
    public function query(Query $query): QueryResult;
}