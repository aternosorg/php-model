<?php

namespace Aternos\Model\Query;

/**
 * Class WhereCondition
 *
 * @package Aternos\Model\Query
 */
class WhereCondition
{
    /**
     * Name of the field
     *
     * @var string
     */
    public $field;

    /**
     * Operator between field and value
     *
     * @var string
     */
    public $operator = "=";

    /**
     * Field value
     *
     * @var mixed
     */
    public $value;

    /**
     * @var bool
     */
    public $fieldRaw = false;

    /**
     * @var bool
     */
    public $valueRaw = false;

    /**
     * WhereCondition constructor.
     *
     * @param string $field
     * @param mixed $value
     * @param string $operator
     */
    public function __construct(string $field = null, $value = null, string $operator = "=")
    {
        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
    }

    /**
     * @param bool $fieldRaw
     * @return WhereCondition
     */
    public function setFieldRaw(bool $fieldRaw = true): WhereCondition
    {
        $this->fieldRaw = $fieldRaw;
        return $this;
    }

    /**
     * @param bool $valueRaw
     * @return WhereCondition
     */
    public function setValueRaw(bool $valueRaw = true): WhereCondition
    {
        $this->valueRaw = $valueRaw;
        return $this;
    }
}