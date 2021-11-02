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
     * @var SelectField[]
     */
    protected ?array $fields = null;
    protected ?array $group = null;

    /**
     * SelectQuery constructor.
     *
     * @param array|null|WhereCondition|WhereGroup $where
     * @param array|null $order
     * @param array|null $fields
     * @param array|null|int|Limit $limit
     * @param array|null|string[]|GroupField[] $group
     */
    public function __construct($where = null, $order = null, $fields = null, $limit = null, $group = null)
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

        if ($group) {
            $this->groupBy($group);
        }
    }

    /**
     * Set fields
     *
     * @param array $fields
     * @return SelectQuery
     */
    public function fields(array $fields)
    {
        parent::fields($fields);

        $this->fields = [];
        foreach ($fields as $key => $field) {
            if ($field instanceof SelectField) {
                $this->fields[] = $field;
            } else if (is_string($field)) {
                $this->fields[] = new SelectField($field);
            }
        }

        return $this;
    }

    /**
     * Set group by fields
     *
     * @param array|GroupField[]|string[] $fields
     */
    public function groupBy(array $fields)
    {
        $this->group = null;
        foreach ($fields as $key => $field) {
            if ($field instanceof GroupField) {
                $this->group[] = $field;
            } else if (is_string($field)) {
                $this->group[] = new GroupField($field);
            }
        }
    }

    /**
     * @return array|null|GroupField[]
     */
    public function getGroup(): ?array
    {
        return $this->group;
    }
}