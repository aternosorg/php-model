<?php

namespace Aternos\Model\Query;

use BadMethodCallException;

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
     * @param array|WhereCondition|WhereGroup|null $where
     * @param array|null $order
     * @param array|int|Limit|null $limit
     */
    public function __construct(null|WhereCondition|array|WhereGroup $where = null,
                                null|array                           $order = null,
                                null|Limit|array|int                 $limit = null)
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
     * @param array $fields
     */
    public function fields(array $fields): static
    {
        throw new BadMethodCallException("You can't set fields on a delete query.");
    }
}