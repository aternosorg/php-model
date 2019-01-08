<?php

namespace Aternos\Model\Query;

/**
 * Class RelationalQuery
 *
 * @author Matthias Neid
 * @package Aternos\Model\Query
 */
class RelationalQuery extends Query
{
    protected $where;

    public function __construct(array $where, array $order, int $limit = -1)
    {

    }
}