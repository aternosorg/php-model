<?php

namespace Aternos\Model\Query;

/**
 * Class DeleteQuery
 *
 * @package Aternos\Model\Query
 */
class DeleteQuery extends Query
{
    /**
     * DeleteQuery constructor.
     *
     * @param array|null|WhereCondition|WhereGroup $where
     * @param array|null $order
     * @param array|null|int|Limit $limit
     */
    public function __construct($where = null, $order = null, $limit = null)
    {
        if ($where) {
            $this->where($where);
        }

        if ($order) {
            $this->orderBy($order);
        }

        if ($limit) {
            $this->limit($limit);
        }
    }

    /**
     * @param $fields
     * @return Query|void
     */
    public function fields($fields)
    {
        throw new \BadMethodCallException("You can't set fields on a delete query.");
    }
}