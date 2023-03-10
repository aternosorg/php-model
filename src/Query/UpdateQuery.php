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
     * @var UpdateField[]
     */
    protected ?array $fields = null;

    /**
     * UpdateQuery constructor.
     *
     * @param array|null $fields
     * @param array|WhereCondition|WhereGroup|null $where
     * @param array|null $order
     * @param array|int|Limit|null $limit
     */
    public function __construct(null|array                           $fields = null,
                                null|WhereCondition|array|WhereGroup $where = null,
                                null|array                           $order = null,
                                null|Limit|array|int                 $limit = null)
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

    /**
     * Set fields
     *
     * @param array $fields
     * @return $this
     */
    public function fields(array $fields): static
    {
        $this->fields = [];
        foreach ($fields as $key => $field) {
            if ($field instanceof UpdateField) {
                $this->fields[] = $field;
            } else if (is_string($key)) {
                $this->fields[] = new UpdateField($key, $field);
            }
        }

        return $this;
    }
}