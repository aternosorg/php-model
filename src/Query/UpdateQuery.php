<?php

namespace Aternos\Model\Query;

/**
 * Class UpdateQuery
 *
 * @package Aternos\Model\Query
 */
class UpdateQuery extends Query
{
    /**
     * UpdateQuery constructor.
     *
     * @param array|null $fields
     * @param array|null|WhereCondition|WhereGroup $where
     * @param array|null $order
     * @param array|null|int|Limit $limit
     */
    public function __construct($fields = null, $where = null, $order = null, $limit = null)
    {
        if ($fields) {
            $this->fields($fields);
        }

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
}