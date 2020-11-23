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
     * @var string|null
     */
    public ?string $field = null;

    /**
     * Operator between field and value
     *
     * @var string
     */
    public string $operator = "=";

    /**
     * Field value
     *
     * @var mixed
     */
    public $value;

    /**
     * @var bool
     */
    public bool $fieldRaw = false;

    /**
     * @var bool
     */
    public bool $valueRaw = false;

    /**
     * WhereCondition constructor.
     *
     * @param string|null $field
     * @param mixed $value
     * @param string $operator
     */
    public function __construct(?string $field = null, $value = null, string $operator = "=")
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