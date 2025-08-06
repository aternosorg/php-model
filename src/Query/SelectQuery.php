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
     * @param array|WhereCondition|WhereGroup|null $where
     * @param array|null $order
     * @param array|null $fields
     * @param array|int|Limit|null $limit
     * @param array|GroupField[]|string[]|null $group
     * @param bool $saveResultsToRegistry Whether results of this query should be saved in the model registry.
     */
    public function __construct(null|WhereCondition|array|WhereGroup $where = null,
                                null|array                           $order = null,
                                null|array                           $fields = null,
                                null|Limit|array|int                 $limit = null,
                                null|array                           $group = null,
                                protected bool                       $saveResultsToRegistry = true)
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
     * @return $this
     */
    public function fields(array $fields): static
    {
        $this->fields = [];
        foreach ($fields as $field) {
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
     * @return $this
     */
    public function groupBy(array $fields): static
    {
        $this->group = null;
        foreach ($fields as $field) {
            if ($field instanceof GroupField) {
                $this->group[] = $field;
            } else if (is_string($field)) {
                $this->group[] = new GroupField($field);
            }
        }
        return $this;
    }

    /**
     * @return array|null|GroupField[]
     */
    public function getGroup(): ?array
    {
        return $this->group;
    }

    /**
     * Set whether results of this query should be saved in the model registry
     *
     * @param bool $saveResultsToRegistry
     * @return $this
     */
    public function saveResultsToRegistry(bool $saveResultsToRegistry = true): static
    {
        $this->saveResultsToRegistry = $saveResultsToRegistry;
        return $this;
    }

    /**
     * Whether results of this query should be saved in the model registry
     *
     * @return bool
     */
    public function shouldSaveResultsToRegistry(): bool
    {
        return $this->saveResultsToRegistry;
    }
}
