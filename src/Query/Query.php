<?php

namespace Aternos\Model\Query;

/**
 * Class Query
 *
 * @package Aternos\Model\Query
 */
abstract class Query
{
    /**
     * @var WhereGroup|null
     */
    protected ?WhereGroup $where = null;

    /**
     * @var array|null
     */
    protected ?array $order = null;

    /**
     * @var Field[]
     */
    protected ?array $fields = null;

    /**
     * @var Limit|null
     */
    protected ?Limit $limit = null;

    /**
     * @var string|null
     */
    public ?string $modelClassName = null;

    /**
     * Set the WHERE part of the query
     *
     * Can be either a WhereCondition, a WhereGroup or an array, e.g.
     *
     * SIMPLE:   ['field' => 'value', 'anotherField' => 'anotherValue']
     * OPERATOR: [['field', '=', 'value'], ['anotherField', '>', 'anotherValue']]
     * MIXED:    ['field' => 'value', ['anotherField', '>', 'anotherValue']]
     *
     * @param array|WhereCondition|WhereGroup $where
     * @return Query
     */
    public function where($where)
    {
        if (is_array($where)) {
            $group = new WhereGroup();
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    if (count($value) === 2) {
                        $group->add(new WhereCondition($value[0], $value[1]));
                    } elseif (count($value) === 3) {
                        $group->add(new WhereCondition($value[0], $value[2], $value[1]));
                    } else {
                        throw new \InvalidArgumentException('Argument $where has an invalid array element with a length of ' . count($value) . '.');
                    }
                } else {
                    $group->add(new WhereCondition($key, $value));
                }
            }
            $this->where = $group;
        } else if ($where instanceof WhereCondition) {
            $this->where = new WhereGroup([$where]);
        } else if ($where instanceof WhereGroup) {
            $this->where = $where;
        }

        return $this;
    }

    /**
     * Get WHERE part of the query
     *
     * @return WhereGroup|bool
     */
    public function getWhere()
    {
        if ($this->where) {
            return $this->where;
        }

        return false;
    }

    /**
     * Set the ORDER part of the query
     *
     * Should be an array containing OrderField (s) or plaintext definitions e.g.
     *
     * ['field' => 'ASC', 'anotherField' => 'DESC', 'usingConstantsField' => OrderField::ASCENDING]
     *
     * @param array $order
     * @return Query
     */
    public function orderBy(array $order)
    {
        $this->order = [];
        foreach ($order as $key => $value) {
            if ($value instanceof OrderField) {
                $this->order[] = $value;
                continue;
            }

            if (!is_int($value)) {
                switch (strtoupper($value)) {
                    case "ASCENDING":
                    case "ASC":
                        $value = OrderField::ASCENDING;
                        break;
                    case "DESCENDING":
                    case "DESC":
                        $value = OrderField::DESCENDING;
                        break;
                    default:
                        throw new \InvalidArgumentException('Argument $order contains invalid order direction: ' . $value);
                }
            }

            $this->order[] = new OrderField($key, $value);
        }

        return $this;
    }

    /**
     * Get ORDER part of the query
     *
     * @return array|bool
     */
    public function getOrder(): ?array
    {
        return $this->order;
    }

    /**
     * Set the fields of the query
     *
     * Can be either an array of keys, of key value pairs or of Field objects
     *
     * ['field', 'anotherfield']
     * ['field' => 'value', 'anotherfield' => 'anothervalue']
     * [new Field('field'), new Field('anotherfield', 'anothervalue')]
     *
     * @param array $fields
     * @return Query
     */
    public function fields(array $fields)
    {
        if (!is_array($fields)) {
            throw new \InvalidArgumentException('Argument $fields is not an array.');
        }

        return $this;
    }

    /**
     * Get SELECT fields of the query
     *
     * @return array|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * Set the LIMIT of the query
     *
     * Can be either a Limit object or an int or an array
     *
     * Int sets only the length (start is 0)
     * Array sets both [start, length] (e.g. [5, 10])
     *
     * @param int|array|Limit $limit
     * @return Query
     */
    public function limit($limit)
    {
        if (is_int($limit)) {
            $this->limit = new Limit($limit);
        } elseif (is_array($limit)) {
            $this->limit = new Limit($limit[1], $limit[0]);
        } elseif ($limit instanceof Limit) {
            $this->limit = $limit;
        } else {
            throw new \InvalidArgumentException('Argument $limit has an invalid type.');
        }

        return $this;
    }

    /**
     * Get the LIMIT of the query
     *
     * @return Limit|null
     */
    public function getLimit(): ?Limit
    {
        return $this->limit;
    }
}