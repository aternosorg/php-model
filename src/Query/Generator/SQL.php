<?php

namespace Aternos\Model\Query\Generator;

use Aternos\Model\Query\{DeleteQuery, Field, OrderField, Query, SelectQuery, UpdateQuery, WhereCondition, WhereGroup};

/**
 * Class SQL
 *
 * @package Aternos\Model\Query\Generator
 */
class SQL implements QueryGeneratorInterface
{
    /**
     * Enclose strings in query with this character
     *
     * @var string
     */
    public $stringEnclosure = "'";

    /**
     * Enclose tables in query with this character
     *
     * @var string
     */
    public $tableEnclosure = "`";

    /**
     * Enclose columns in query with this character
     *
     * @var string
     */
    public $columnEnclosure = "`";

    /**
     * Callback function to escape values
     *
     * @var \Closure
     */
    public $escapeFunction = "addslashes";

    /**
     * SQL constructor.
     *
     * @param \Closure $escapeFunction
     */
    public function __construct($escapeFunction)
    {
        $this->escapeFunction = $escapeFunction;
    }

    /**
     * Generate a query string from a Query object
     *
     * @param Query $query
     * @return string
     */
    public function generate(Query $query): string
    {
        $queryString = "";
        if ($query instanceof SelectQuery) {
            $queryString .= "SELECT ";

            if ($query->getFields()) {
                $queryString .= " " . $this->generateFields($query);
            } else {
                $queryString .= " *";
            }

            $queryString .= " FROM " . $this->tableEnclosure . $query->modelClassName::getName() . $this->tableEnclosure;
        } else if ($query instanceof UpdateQuery) {
            $queryString .= "UPDATE " . $this->tableEnclosure . $query->modelClassName::getName() . $this->tableEnclosure . " SET";
            $queryString .= " " . $this->generateFields($query);
        } else if ($query instanceof DeleteQuery) {
            $queryString .= "DELETE  FROM " . $this->tableEnclosure . $query->modelClassName::getName() . $this->tableEnclosure;
        }

        if ($query->getWhere()) {
            $queryString .= " WHERE " . $this->generateWhere($query);
        }

        if ($query->getOrder()) {
            $queryString .= " " . $this->generateOrder($query);
        }

        if ($limit = $query->getLimit()) {
            $queryString .= " LIMIT " . $limit->start . ", " . $limit->length;
        }

        return $queryString;
    }

    /**
     * Generate query from where conditions and groups
     *
     * @param Query $query
     * @param WhereGroup|WhereCondition|null $where
     * @return string
     */
    private function generateWhere(Query $query, $where = null)
    {
        if (!$where) {
            $where = $query->getWhere();
        }
        if ($where instanceof WhereCondition) {
            $value = $this->generateValue($where->value);

            return $this->columnEnclosure . $where->field . $this->columnEnclosure . " " . $where->operator . " " . $value;
        } elseif ($where instanceof WhereGroup) {
            switch ($where->conjunction) {
                case WhereGroup:: AND:
                    $conjunction = " AND ";
                    break;
                case WhereGroup:: OR:
                    $conjunction = " OR ";
                    break;
                default:
                    throw new \UnexpectedValueException("Invalid conjunction: " . $where->conjunction);
            }

            $whereStrings = [];
            foreach ($where as $wherePart) {
                $whereStrings[] = $this->generateWhere($query, $wherePart);
            }

            return "(" . implode($conjunction, $whereStrings) . ")";
        }
    }

    /**
     * Generate query from order field definitions
     *
     * @param Query $query
     * @return string
     */
    private function generateOrder(Query $query)
    {
        $orderFields = $query->getOrder();

        $return = "ORDER BY";

        $formattedOrderFields = [];
        foreach ($orderFields as $orderField) {
            /** @var OrderField $orderField */
            switch ($orderField->direction) {
                case OrderField::ASCENDING:
                    $direction = "ASC";
                    break;
                case OrderField::DESCENDING:
                    $direction = "DESC";
                    break;
                default:
                    throw new \UnexpectedValueException("Invalid direction: " . $orderField->direction);
            }

            $formattedOrderFields[] = $this->columnEnclosure . $orderField->field . $this->columnEnclosure . " " . $direction;
        }

        $return .= " " . implode(", ", $formattedOrderFields);

        return $return;
    }

    /**
     * Generate fields for select or update queries
     *
     * @param Query $query
     * @return string
     */
    private function generateFields(Query $query)
    {
        $fields = $query->getFields();

        $fieldStrings = [];
        foreach ($fields as $field) {
            /** @var Field $field */
            if ($query instanceof SelectQuery) {
                $fieldStrings[] = $this->columnEnclosure . $field->key . $this->columnEnclosure;
            } else if ($query instanceof UpdateQuery) {
                $fieldStrings[] = $this->columnEnclosure . $field->key . $this->columnEnclosure . "=" . $this->generateValue($field->value);
            }
        }

        return implode(", ", $fieldStrings);
    }

    /**
     * Generate value for where ore field usage
     *
     * @param $value
     * @return string
     */
    private function generateValue($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_null($value)) {
            return "NULL";
        }

        $value = ($this->escapeFunction)($value);

        return $this->stringEnclosure . $value . $this->stringEnclosure;
    }
}