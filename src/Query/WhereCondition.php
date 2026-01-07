<?php

namespace Aternos\Model\Query;

use UnexpectedValueException;

/**
 * Class WhereCondition
 *
 * @package Aternos\Model\Query
 */
class WhereCondition implements Validatable
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
    public mixed $value;

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
     * @param mixed|null $value
     * @param string $operator
     */
    public function __construct(?string $field = null, mixed $value = null, string $operator = "=")
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

    public function validate(): void
    {
        if ($this->operator === "IN" || $this->operator === "NOT IN") {
            if (!is_array($this->value)) {
                throw new UnexpectedValueException("Value for IN or NOT IN operator must be an array.");
            }

            if (count($this->value) === 0) {
                throw new UnexpectedValueException("Value array for IN or NOT IN operator must not be empty.");
            }
        }
    }
}
