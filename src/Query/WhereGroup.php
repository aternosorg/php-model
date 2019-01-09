<?php

namespace Aternos\Model\Query;

/**
 * Class WhereConditionGroup
 *
 * @package Aternos\Model\Query
 */
class WhereGroup implements \Iterator, \Countable
{
    /**
     * Conjunction values
     */
    const AND = 0;
    const OR = 1;

    /**
     * Multiple WhereGroup or WhereCondition objects
     *
     * @var array
     */
    protected $group = [];

    /**
     * @var int
     */
    public $conjunction = self:: AND;

    /**
     * Group iterator
     *
     * @var int
     */
    private $iterator = 0;

    /**
     * WhereGroup constructor.
     *
     * @param array $conditions
     * @param int $conjunction
     */
    public function __construct($conditions = [], $conjunction = self:: AND)
    {
        if (!is_array($conditions)) {
            throw new \InvalidArgumentException('Argument $conditions should be an array.');
        }

        $this->group = $conditions;
        $this->conjunction = $conjunction;
    }

    /**
     * Add an element to the group
     *
     * @param WhereGroup|WhereCondition $conditionOrGroup
     */
    public function add($conditionOrGroup)
    {
        if (!$conditionOrGroup instanceof WhereCondition && !$conditionOrGroup instanceof WhereGroup) {
            throw new \InvalidArgumentException('Argument $conditionOrGroup has to be instance of WhereCondition or WhereGroup.');
        }

        $this->group[] = $conditionOrGroup;
    }

    /**
     * Get all group elements as array
     *
     * @return array
     */
    public function getAll()
    {
        return $this->group;
    }

    /**
     * Return the current element
     *
     * @return WhereGroup|WhereCondition
     */
    public function current()
    {
        return $this->group[$this->iterator];
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        $this->iterator++;
    }

    /**
     * Return the key of the current element
     *
     * @return int
     */
    public function key()
    {
        return $this->iterator;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return array_key_exists($this->iterator, $this->group);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
        $this->iterator = 0;
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->group);
    }
}