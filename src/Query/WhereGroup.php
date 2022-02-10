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
    protected array $group = [];

    /**
     * @var int
     */
    public int $conjunction = self:: AND;

    /**
     * Group iterator
     *
     * @var int
     */
    protected int $iterator = 0;

    /**
     * WhereGroup constructor.
     *
     * @param array $conditions
     * @param int $conjunction
     */
    public function __construct(array $conditions = [], int $conjunction = self:: AND)
    {
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
    public function getAll(): array
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
    public function key(): int
    {
        return $this->iterator;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid(): bool
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
    public function count(): int
    {
        return count($this->group);
    }
}