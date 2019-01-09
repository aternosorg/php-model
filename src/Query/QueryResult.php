<?php

namespace Aternos\Model\Query;

use Aternos\Model\ModelInterface;

/**
 * Class QueryResult
 *
 * @author Matthias Neid
 * @package Aternos\Model\Query
 */
class QueryResult implements \Iterator, \Countable
{
    /**
     * Success state of the query
     *
     * @var bool
     */
    private $success;

    /**
     * @var array
     */
    private $result = [];

    /**
     * Iterator for $result
     *
     * @var int
     */
    private $iterator = 0;

    /**
     * QueryResult constructor.
     *
     * @param bool $success
     * @param array $result Containing models (ModelInterface)
     */
    public function __construct(bool $success, $result = [])
    {
        $this->success = $success;
        if (is_array($result)) {
            $this->result = $result;
        }
    }

    /**
     * Add a model to the result set
     *
     * @param ModelInterface $model
     */
    public function add(ModelInterface $model)
    {
        $this->result[] = $model;
    }

    /**
     * Return the current element
     *
     * @return ModelInterface
     */
    public function current()
    {
        return $this->result[$this->iterator];
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
        return array_key_exists($this->iterator, $this->result);
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
        return count($this->result);
    }
}