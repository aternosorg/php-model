<?php

namespace Aternos\Model\Query;

use Aternos\Model\ModelInterface;

/**
 * Class QueryResult
 *
 * @author Matthias Neid
 * @package Aternos\Model\Query
 */
class QueryResult implements \Iterator, \Countable, \ArrayAccess
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
     * Check if the query was successful
     *
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return (bool)$this->success;
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

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->result[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->result[$offset];
    }

    /**
     * Offset to set
     *
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->result[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->result[$offset]);
    }
}