<?php

namespace Aternos\Model\Driver;

use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;

/**
 * Interface QueryableDriver
 *
 * @package Aternos\Model\Driver
 */
interface QueryableDriverInterface extends DriverInterface
{
    /**
     * Execute a SELECT, UPDATE or DELETE query
     *
     * @param Query $query
     * @return QueryResult
     */
    public function query(Query $query): QueryResult;
}