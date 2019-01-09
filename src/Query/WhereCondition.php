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
}