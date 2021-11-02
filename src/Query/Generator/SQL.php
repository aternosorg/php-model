<?php

namespace Aternos\Model\Query\Generator;

use Aternos\Model\Query\{DeleteQuery,
    OrderField,
    Query,
    SelectField,
    SelectQuery,
    UpdateField,
    UpdateQuery,
    WhereCondition,
    WhereGroup
};

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
    public string $stringEnclosure = "'";

    /**
     * Enclose tables in query with this character
     *
     * @var string
     */
    public string $tableEnclosure = "`";

    /**
     * Enclose columns in query with this character
     *
     * @var string
     */
    public string $columnEnclosure = "`";

    /**
     * Callback function to escape values
     *
     * @var callable|string
     */
    public $escapeFunction = "addslashes";

    /**
     * SQL constructor.
     *
     * @param callable $escapeFunction
     */
    public function __construct(callable $escapeFunction)
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
            $queryString .= "SELECT";

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
            $queryString .= "DELETE FROM " . $this->tableEnclosure . $query->modelClassName::getName() . $this->tableEnclosure;
        }

        if ($query->getWhere()) {
            $queryString .= " WHERE " . $this->generateWhere($query);
        }

        $queryString .= $this->generateGroup($query);

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
            if ($where->value === null && $where->operator === "=") {
                $where->operator = "IS";
            }

            $value = $where->value;
            if (!$where->valueRaw) {
                $value = $this->generateValue($value);
            }

            $field = $where->field;
            if (!$where->fieldRaw) {
                $field = $this->columnEnclosure . $field . $this->columnEnclosure;
            }

            return $field . " " . $where->operator . " " . $value;
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

        return "";
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

            if ($orderField->raw) {
                $formattedOrderFields[] = $orderField->field . " " . $direction;
            } else {
                $formattedOrderFields[] = $this->columnEnclosure . $orderField->field . $this->columnEnclosure . " " . $direction;
            }

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
            if ($field instanceof SelectField) {
                if ($field->raw === true) {
                    $fieldStrings[] = $field->key;
                } else {
                    $fieldString = "";
                    if ($field->function !== null) {
                        switch ($field->function) {
                            case SelectField::COUNT:
                                $fieldString .= "COUNT(";
                                break;
                            case SelectField::SUM:
                                $fieldString .= "SUM(";
                                break;
                            case SelectField::AVERAGE:
                                $fieldString .= "AVG(";
                                break;
                        }
                    }

                    $fieldString .= $this->columnEnclosure . $field->key . $this->columnEnclosure;

                    if ($field->function !== null) {
                        $fieldString .= ")";
                    }

                    if ($field->alias !== null) {
                        $fieldString .= " AS " . $this->columnEnclosure . $field->alias . $this->columnEnclosure;
                    }
                    $fieldStrings[] = $fieldString;
                }

            } else if ($field instanceof UpdateField) {
                $fieldStrings[] = $this->columnEnclosure . $field->key . $this->columnEnclosure . "=" . $this->generateValue($field->value);
            }
        }

        return implode(", ", $fieldStrings);
    }

    /**
     * @param Query $query
     * @return string
     */
    protected function generateGroup(Query $query): string
    {
        if (!$query instanceof SelectQuery || $query->getGroup() === null || count($query->getGroup()) === 0) {
            return "";
        }

        $groupFieldStrings = [];
        foreach ($query->getGroup() as $groupField) {
            $groupFieldStrings[] = $this->columnEnclosure . $groupField->key . $this->columnEnclosure;
        }

        return " GROUP BY " . implode(", ", $groupFieldStrings);
    }

    /**
     * Generate value for where ore field usage
     *
     * @param string|int|float|null $value
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