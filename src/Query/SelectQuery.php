<?php

namespace Aternos\Model\Query;

/**
 * Class SelectQuery
 *
 * @package Aternos\Model\Query
 */
class SelectQuery extends Query
{
    /**
     * SelectQuery constructor.
     *
     * @param array|null|WhereCondition|WhereGroup $where
     * @param array|null $order
     * @param array|null $fields
     * @param array|null|int|Limit $limit
     */
    public function __construct($where = null, $order = null, $fields = null, $limit = null)
    {
        if ($where) {
            $this->where($where);
        }

        if ($order) {
            $this->orderBy($order);
        }

        if ($fields) {
            $this->fields($fields);
        }

        if ($limit) {
            $this->limit($limit);
        }
    }
}