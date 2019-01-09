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
            $this->setWhere($where);
        }

        if ($order) {
            $this->setOrder($order);
        }

        if ($fields) {
            $this->setFields($fields);
        }

        if ($limit) {
            $this->setLimit($limit);
        }
    }
}